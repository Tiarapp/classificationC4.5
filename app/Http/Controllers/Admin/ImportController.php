<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\KuesionerImport;
use App\Models\Dataset;
use App\Models\Respondent;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.index');
    }

    public function uploadForm()
    {
        return view('admin.import.upload');
    }
    
    public function simpleForm()
    {
        return view('admin.import.simple');
    }

    public function processImport(Request $request)
    {
        // Debug: Log upload attempt
        \Log::info('Upload attempt started', [
            'files' => $request->hasFile('file'),
            'file_count' => count($request->allFiles()),
            'all_inputs' => $request->all()
        ]);
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            'replace_existing' => 'boolean'
        ]);

        // Set execution limits for large imports
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');
        
        try {
            DB::beginTransaction();

            // Jika replace_existing = true, hapus data lama
            if ($request->boolean('replace_existing')) {
                $this->clearExistingData();
            }

            // Store uploaded file temporarily
            $file = $request->file('file');
            
            // Debug file info
            \Log::info('File details', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'temp_path' => $file->getPathname(),
                'is_valid' => $file->isValid()
            ]);
            
            $filename = 'import_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Try manual file copy instead of Laravel's storeAs
            try {
                $destinationPath = storage_path('app/imports/' . $filename);
                $sourcePath = $file->getPathname();
                
                \Log::info('Manual file copy attempt', [
                    'source' => $sourcePath,
                    'destination' => $destinationPath,
                    'source_exists' => file_exists($sourcePath),
                    'source_size' => file_exists($sourcePath) ? filesize($sourcePath) : 'N/A'
                ]);
                
                // Ensure imports directory exists
                $importsDir = storage_path('app/imports');
                if (!is_dir($importsDir)) {
                    mkdir($importsDir, 0755, true);
                }
                
                // Manual copy
                $copySuccess = copy($sourcePath, $destinationPath);
                
                \Log::info('Manual copy result', [
                    'success' => $copySuccess,
                    'destination_exists' => file_exists($destinationPath),
                    'destination_size' => file_exists($destinationPath) ? filesize($destinationPath) : 'N/A'
                ]);
                
                if (!$copySuccess || !file_exists($destinationPath)) {
                    throw new \Exception("Manual file copy failed");
                }
                
                $path = 'imports/' . $filename;
                
            } catch (\Exception $storageException) {
                \Log::error('File storage failed', [
                    'error' => $storageException->getMessage(),
                    'filename' => $filename
                ]);
                throw $storageException;
            }

            // Process import with progress tracking
            $import = new KuesionerImport();
            Excel::import($import, storage_path('app/' . $path));

            // Clean up temporary file
            Storage::disk('local')->delete($path);

            DB::commit();

            // Prepare result data
            $results = [
                'success' => true,
                'message' => 'Import berhasil!',
                'success_count' => $import->getSuccessCount(),
                'error_count' => $import->getErrorCount(),
                'errors' => $import->getErrors(),
                'total_respondents' => Respondent::count(),
                'total_datasets' => Dataset::count()
            ];

            return view('admin.import.result', compact('results'));

        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up file if exists
            if (isset($path)) {
                Storage::disk('local')->delete($path);
            }

            return back()->withErrors([
                'file' => 'Import gagal: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'nama',
            'nim', 
            'jurusan',
            'semester',
            'durasi_penggunaan',
            'frekuensi_akses',
            'perhatian_konten',
            'penghayatan',
            'label_intensitas'
        ];

        $sampleData = [
            [
                'nama' => 'John Doe',
                'nim' => '2201001',
                'jurusan' => 'Teknik Informatika',
                'semester' => 5,
                'durasi_penggunaan' => '1-3 jam',
                'frekuensi_akses' => '3-5x',
                'perhatian_konten' => 3,
                'penghayatan' => 4,
                'label_intensitas' => 'sedang'
            ],
            [
                'nama' => 'Jane Smith',
                'nim' => '2201002',
                'jurusan' => 'Sistem Informasi', 
                'semester' => 3,
                'durasi_penggunaan' => '<=1 jam',
                'frekuensi_akses' => '1-2x',
                'perhatian_konten' => 2,
                'penghayatan' => 1,
                'label_intensitas' => 'rendah'
            ],
            [
                'nama' => 'Bob Wilson',
                'nim' => '2201003',
                'jurusan' => 'Manajemen',
                'semester' => 7,
                'durasi_penggunaan' => '>5 jam',
                'frekuensi_akses' => '>5x',
                'perhatian_konten' => 5,
                'penghayatan' => 5,
                'label_intensitas' => 'tinggi'
            ]
        ];

        // Create collection with headers and sample data
        $data = collect([$headers]);
        foreach ($sampleData as $row) {
            $data->push(array_values($row));
        }

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection {
            protected $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function collection() {
                return $this->data;
            }
        }, 'template_import_kuesioner.xlsx');
    }

    public function getImportHistory()
    {
        // Get recent import statistics
        $stats = [
            'total_respondents' => Respondent::count(),
            'total_datasets' => Dataset::count(),
            'distribution' => Dataset::selectRaw('label_intensitas, COUNT(*) as count')
                ->groupBy('label_intensitas')
                ->get()
                ->pluck('count', 'label_intensitas'),
            'recent_imports' => Dataset::with('respondent')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json($stats);
    }

    private function clearExistingData()
    {
        // Delete in correct order due to foreign key constraints
        Dataset::truncate();
        Respondent::truncate();
        
        // Reset auto increment
        DB::statement('ALTER TABLE datasets AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE respondents AUTO_INCREMENT = 1');
    }

    public function validateFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $file = $request->file('file');
            
            // Quick validation - check first few rows
            $data = Excel::toArray([], $file);
            
            if (empty($data) || empty($data[0])) {
                return response()->json([
                    'valid' => false,
                    'message' => 'File kosong atau tidak valid'
                ]);
            }

            $headers = array_map('strtolower', array_map('trim', $data[0][0] ?? []));
            $requiredHeaders = ['nama', 'durasi_penggunaan', 'frekuensi_akses', 'label_intensitas'];
            
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Header tidak lengkap. Missing: ' . implode(', ', $missingHeaders),
                    'required_headers' => $requiredHeaders,
                    'found_headers' => $headers
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'File valid siap diimport',
                'row_count' => count($data[0]) - 1, // Exclude header
                'headers' => $headers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validasi file: ' . $e->getMessage()
            ]);
        }
    }
    
    public function testUpload(Request $request)
    {
        \Log::info('Test upload attempt', [
            'has_file' => $request->hasFile('file'),
            'files_count' => count($request->allFiles()),
            'all_data' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length')
        ]);
        
        if (!$request->hasFile('file')) {
            return back()->withErrors(['file' => 'No file uploaded'])->withInput();
        }
        
        $file = $request->file('file');
        
        \Log::info('File uploaded successfully', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_valid' => $file->isValid(),
            'temp_path' => $file->getPathname()
        ]);
        
        return back()->with('success', 'File uploaded successfully: ' . $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)');
    }
    
    public function testForm()
    {
        return view('admin.import.test');
    }
}