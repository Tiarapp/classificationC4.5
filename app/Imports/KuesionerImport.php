<?php

namespace App\Imports;

use App\Models\Respondent;
use App\Models\Dataset;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class KuesionerImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Log row data untuk debugging
                \Log::info("Processing import row " . ($index + 1), $row->toArray());
                
                // Buat atau cari responden
                $responden = $this->createOrFindResponden($row);
                
                // Buat dataset untuk responden
                $this->createDataset($responden, $row);
                
                $this->successCount++;
                
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $index + 2, // +2 karena header row dan index mulai dari 0
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
                $this->errorCount++;
                
                Log::error("Import error on row " . ($index + 2), [
                    'error' => $e->getMessage(),
                    'data' => $row->toArray(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    private function createOrFindResponden($row)
    {
        // Cari berdasarkan NIM, jika tidak ada buat baru
        $nim = $this->normalizeNim($row['nim'] ?? null);
        
        return Respondent::updateOrCreate(
            ['nim' => $nim],
            [
                'nama' => $this->normalizeName($row['nama'] ?? null),
                'jurusan' => $this->normalizeJurusan($row['jurusan'] ?? null),
                'semester' => $this->normalizeSemester($row['semester'] ?? null)
            ]
        );
    }

    private function normalizeNim($nim)
    {
        if (empty($nim)) {
            return $this->generateNIM();
        }
        
        // Convert to string and clean
        $nim = (string) $nim;
        $nim = preg_replace('/[^0-9]/', '', $nim); // Remove non-numeric
        
        if (empty($nim) || strlen($nim) < 5) {
            return $this->generateNIM();
        }
        
        return $nim;
    }

    private function normalizeName($name)
    {
        if (empty($name)) {
            return 'Responden ' . time() . rand(100, 999);
        }
        
        return (string) $name;
    }

    private function normalizeJurusan($jurusan) 
    {
        if (empty($jurusan)) {
            return 'Tidak Diketahui';
        }
        
        return (string) $jurusan;
    }

    private function normalizeSemester($semester)
    {
        if (empty($semester)) {
            return rand(1, 8);
        }
        
        // Convert to integer
        $semester = (int) $semester;
        
        // Validate range
        if ($semester < 1 || $semester > 12) {
            return rand(1, 8);
        }
        
        return $semester;
    }

    private function createDataset($responden, $row)
    {
        // Mapping kolom Excel ke field database
        $data = [
            'respondent_id' => $responden->id,
            
            // 4 atribut utama C4.5
            'durasi_penggunaan' => $this->normalizeDurasi($row['durasi_penggunaan'] ?? $row['durasi'] ?? null),
            'frekuensi_akses' => $this->normalizeFrekuensi($row['frekuensi_akses'] ?? $row['frekuensi'] ?? null),
            'perhatian_konten' => $this->normalizeNumeric($row['perhatian_konten'] ?? $row['perhatian'] ?? null, 1, 5),
            'penghayatan' => $this->normalizeNumeric($row['penghayatan'] ?? null, 1, 5),
            
            // Label intensitas
            'label_intensitas' => $this->normalizeIntensitas($row['label_intensitas'] ?? $row['intensitas'] ?? $row['label'] ?? null),
            'is_training_data' => true
        ];

        // Validasi data sebelum insert
        $this->validateDatasetData($data);

        return Dataset::create($data);
    }

    private function normalizeNumeric($value, $min = 1, $max = 5)
    {
        if (empty($value)) {
            return rand($min, $max);
        }
        
        // Convert to integer
        $value = (int) $value;
        
        // Validate range
        if ($value < $min || $value > $max) {
            return rand($min, $max);
        }
        
        return $value;
    }

    private function normalizeDurasi($durasi)
    {
        if (empty($durasi) || is_null($durasi)) return '<=1 jam';
        
        $durasi = strtolower(trim((string) $durasi));
        
        // Mapping berbagai format input
        $mappings = [
            '<=1 jam' => '<=1 jam',
            '<= 1 jam' => '<=1 jam', 
            'kurang dari 1 jam' => '<=1 jam',
            'dibawah 1 jam' => '<=1 jam',
            '1' => '<=1 jam',
            
            '1-3 jam' => '1-3 jam',
            '1 - 3 jam' => '1-3 jam',
            'antara 1-3 jam' => '1-3 jam',
            '2' => '1-3 jam',
            
            '3-5 jam' => '3-5 jam',
            '3 - 5 jam' => '3-5 jam',
            'antara 3-5 jam' => '3-5 jam',
            '3' => '3-5 jam',
            
            '>5 jam' => '>5 jam',
            '> 5 jam' => '>5 jam',
            'lebih dari 5 jam' => '>5 jam',
            'diatas 5 jam' => '>5 jam',
            '4' => '>5 jam'
        ];

        return $mappings[$durasi] ?? '<=1 jam';
    }

    private function normalizeFrekuensi($frekuensi)
    {
        if (empty($frekuensi) || is_null($frekuensi)) return '1-2x';
        
        $frekuensi = strtolower(trim((string) $frekuensi));
        
        $mappings = [
            '1-2x' => '1-2x',
            '1-2 kali' => '1-2x',
            '1 sampai 2 kali' => '1-2x',
            '1' => '1-2x',
            '2' => '1-2x',
            
            '3-5x' => '3-5x',
            '3-5 kali' => '3-5x',
            '3 sampai 5 kali' => '3-5x',
            '3' => '3-5x',
            '4' => '3-5x',
            '5' => '3-5x',
            
            '>5x' => '>5x',
            '> 5x' => '>5x',
            'lebih dari 5 kali' => '>5x',
            'diatas 5 kali' => '>5x',
            '6' => '>5x',
            '7' => '>5x'
        ];

        return $mappings[$frekuensi] ?? '1-2x';
    }

    private function normalizeIntensitas($intensitas)
    {
        if (empty($intensitas) || is_null($intensitas)) return 'rendah';
        
        $intensitas = strtolower(trim((string) $intensitas));
        
        $mappings = [
            'rendah' => 'rendah',
            'low' => 'rendah',
            '1' => 'rendah',
            
            'sedang' => 'sedang',
            'medium' => 'sedang',
            'menengah' => 'sedang',
            '2' => 'sedang',
            
            'tinggi' => 'tinggi',
            'high' => 'tinggi',
            '3' => 'tinggi'
        ];

        return $mappings[$intensitas] ?? 'rendah';
    }

    private function validateDatasetData($data)
    {
        // Validasi durasi_penggunaan
        if (!in_array($data['durasi_penggunaan'], ['<=1 jam', '1-3 jam', '3-5 jam', '>5 jam'])) {
            throw new \Exception("Invalid durasi_penggunaan: " . $data['durasi_penggunaan']);
        }

        // Validasi frekuensi_akses
        if (!in_array($data['frekuensi_akses'], ['1-2x', '3-5x', '>5x'])) {
            throw new \Exception("Invalid frekuensi_akses: " . $data['frekuensi_akses']);
        }

        // Validasi perhatian_konten & penghayatan
        if ($data['perhatian_konten'] < 1 || $data['perhatian_konten'] > 5) {
            throw new \Exception("Invalid perhatian_konten: must be between 1-5");
        }

        if ($data['penghayatan'] < 1 || $data['penghayatan'] > 5) {
            throw new \Exception("Invalid penghayatan: must be between 1-5");
        }

        // Validasi label_intensitas
        if (!in_array($data['label_intensitas'], ['rendah', 'sedang', 'tinggi'])) {
            throw new \Exception("Invalid label_intensitas: " . $data['label_intensitas']);
        }
    }

    private function generateNIM()
    {
        $tahun = '22';
        $kode = '01';
        $nomor = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $tahun . $kode . $nomor;
    }

    public function rules(): array
    {
        return [
            // Rules yang lebih fleksibel untuk handle berbagai format input
            '*.nama' => 'nullable|max:255',
            '*.nim' => 'nullable', // Accept any format, will normalize later
            '*.jurusan' => 'nullable|max:255',
            '*.semester' => 'nullable', // Accept any format, will normalize later
            '*.durasi_penggunaan' => 'nullable',
            '*.frekuensi_akses' => 'nullable', 
            '*.perhatian_konten' => 'nullable',
            '*.penghayatan' => 'nullable',
            '*.label_intensitas' => 'nullable'
        ];
    }

    public function chunkSize(): int
    {
        return 100; // Process 100 rows at a time
    }

    public function batchSize(): int
    {
        return 50; // Insert 50 rows at a time
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }
}