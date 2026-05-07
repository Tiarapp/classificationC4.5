<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\Respondent;
use App\Exports\DatasetsExport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class DatasetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $datasets = Dataset::with('respondent')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalDatasets = Dataset::count();
        $trainingData = Dataset::trainingData()->count();
        $testingData = Dataset::testingData()->count();
        $totalResponden = Respondent::count();

        return view('admin.datasets.index', compact('datasets', 'totalDatasets', 'trainingData', 'testingData', 'totalResponden'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $respondents = Respondent::orderBy('nama')->get();
        $dataset = new Dataset();
        
        return view('admin.datasets.create', compact('respondents', 'dataset'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'respondent_id' => 'required|exists:respondents,id',
            'durasi_penggunaan' => 'required|in:<=1 jam,1-3 jam,3-5 jam,>5 jam',
            'frekuensi_akses' => 'required|in:1-2x,3-5x,>5x',
            'perhatian_konten' => 'required|integer|min:1|max:5',
            'penghayatan' => 'required|integer|min:1|max:5',
            'label_intensitas' => 'required|in:rendah,sedang,tinggi',
            'is_training_data' => 'boolean'
        ], [
            'respondent_id.required' => 'Pilih responden terlebih dahulu',
            'respondent_id.exists' => 'Responden tidak valid',
            'durasi_penggunaan.required' => 'Durasi penggunaan wajib dipilih',
            'durasi_penggunaan.in' => 'Durasi penggunaan tidak valid',
            'frekuensi_akses.required' => 'Frekuensi akses wajib dipilih',
            'frekuensi_akses.in' => 'Frekuensi akses tidak valid',
            'perhatian_konten.required' => 'Rating perhatian konten wajib diisi',
            'perhatian_konten.integer' => 'Rating perhatian konten harus berupa angka',
            'perhatian_konten.min' => 'Rating perhatian konten minimal 1',
            'perhatian_konten.max' => 'Rating perhatian konten maksimal 5',
            'penghayatan.required' => 'Rating penghayatan wajib diisi',
            'penghayatan.integer' => 'Rating penghayatan harus berupa angka',
            'penghayatan.min' => 'Rating penghayatan minimal 1',
            'penghayatan.max' => 'Rating penghayatan maksimal 5',
            'label_intensitas.required' => 'Label intensitas wajib dipilih',
            'label_intensitas.in' => 'Label intensitas tidak valid'
        ]);

        $validated['is_training_data'] = $request->has('is_training_data');

        try {
            Dataset::create($validated);
            
            return redirect()->route('admin.datasets.index')
                ->with('success', 'Data dataset berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Dataset $dataset): View
    {
        $dataset->load('respondent');
        return view('admin.datasets.show', compact('dataset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dataset $dataset): View
    {
        $respondents = Respondent::orderBy('nama')->get();
        return view('admin.datasets.edit', compact('dataset', 'respondents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dataset $dataset): RedirectResponse
    {
        $validated = $request->validate([
            'respondent_id' => 'required|exists:respondents,id',
            'durasi_penggunaan' => 'required|in:<=1 jam,1-3 jam,3-5 jam,>5 jam',
            'frekuensi_akses' => 'required|in:1-2x,3-5x,>5x',
            'perhatian_konten' => 'required|integer|min:1|max:5',
            'penghayatan' => 'required|integer|min:1|max:5',
            'label_intensitas' => 'required|in:rendah,sedang,tinggi',
            'is_training_data' => 'boolean'
        ], [
            'respondent_id.required' => 'Pilih responden terlebih dahulu',
            'respondent_id.exists' => 'Responden tidak valid',
            'durasi_penggunaan.required' => 'Durasi penggunaan wajib dipilih',
            'frekuensi_akses.required' => 'Frekuensi akses wajib dipilih',
            'perhatian_konten.required' => 'Rating perhatian konten wajib diisi',
            'perhatian_konten.min' => 'Rating perhatian konten minimal 1',
            'perhatian_konten.max' => 'Rating perhatian konten maksimal 5',
            'penghayatan.required' => 'Rating penghayatan wajib diisi',
            'penghayatan.min' => 'Rating penghayatan minimal 1',
            'penghayatan.max' => 'Rating penghayatan maksimal 5',
            'label_intensitas.required' => 'Label intensitas wajib dipilih'
        ]);

        $validated['is_training_data'] = $request->has('is_training_data');

        try {
            $dataset->update($validated);
            
            return redirect()->route('admin.datasets.index')
                ->with('success', 'Data dataset berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dataset $dataset): RedirectResponse
    {
        try {
            $respondentName = $dataset->respondent->nama;
            $dataset->delete();
            
            return redirect()->route('admin.datasets.index')
                ->with('success', "Data dataset untuk {$respondentName} berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Import datasets from Excel file.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ], [
            'file.required' => 'File Excel wajib dipilih',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV',
            'file.max' => 'Ukuran file maksimal 2MB'
        ]);

        // TODO: Implement Excel import functionality with PHPSpreadsheet
        return redirect()->back()
            ->with('info', 'Fitur import Excel akan segera tersedia!');
    }

    /**
     * Export datasets to Excel file.
     */
    public function export()
    {
        try {
            $export = new DatasetsExport();
            $timestamp = date('Y-m-d_His');
            $filename = "data-kuesioner_{$timestamp}.xlsx";
            
            // Use Excel::download for direct download
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Export datasets error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }
}
