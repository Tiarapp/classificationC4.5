<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Exports\RespondentsExport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RespondentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $respondents = Respondent::with('datasets')
            ->withCount('datasets')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.respondents.index', compact('respondents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.respondents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required|string|max:20|unique:respondents,nim',
            'jurusan' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:14'
        ], [
            'nama.required' => 'Nama wajib diisi',
            'nim.required' => 'NIM wajib diisi',
            'nim.unique' => 'NIM sudah terdaftar',
            'jurusan.required' => 'Jurusan wajib diisi',
            'semester.required' => 'Semester wajib diisi',
            'semester.min' => 'Semester minimal 1',
            'semester.max' => 'Semester maksimal 14'
        ]);

        Respondent::create($validated);

        return redirect()
            ->route('admin.respondents.index')
            ->with('success', 'Data responden berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Respondent $respondent): View
    {
        $respondent->load('datasets');
        
        return view('admin.respondents.show', compact('respondent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Respondent $respondent): View
    {
        return view('admin.respondents.edit', compact('respondent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Respondent $respondent): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required|string|max:20|unique:respondents,nim,' . $respondent->id,
            'jurusan' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:14'
        ], [
            'nama.required' => 'Nama wajib diisi',
            'nim.required' => 'NIM wajib diisi',
            'nim.unique' => 'NIM sudah terdaftar',
            'jurusan.required' => 'Jurusan wajib diisi',
            'semester.required' => 'Semester wajib diisi',
            'semester.min' => 'Semester minimal 1',
            'semester.max' => 'Semester maksimal 14'
        ]);

        $respondent->update($validated);

        return redirect()
            ->route('admin.respondents.index')
            ->with('success', 'Data responden berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Respondent $respondent): RedirectResponse
    {
        // Check if respondent has datasets
        if ($respondent->datasets()->count() > 0) {
            return redirect()
                ->route('admin.respondents.index')
                ->with('error', 'Tidak dapat menghapus responden yang memiliki data kuesioner!');
        }

        $respondent->delete();

        return redirect()
            ->route('admin.respondents.index')
            ->with('success', 'Data responden berhasil dihapus!');
    }

    /**
     * Export respondents to Excel
     */
    public function export()
    {
        try {
            $export = new RespondentsExport();
            $timestamp = date('Y-m-d_His');
            $filename = "data-responden_{$timestamp}.xlsx";
            
            // Use Excel::download for direct download
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Export respondents error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }
}
