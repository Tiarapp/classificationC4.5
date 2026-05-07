@extends('admin.layout')

@section('title', 'Upload File Import (Simple)')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">📤 Upload File Kuesioner (Simple Version)</h4>
                    <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Simple File Upload -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih File Excel/CSV:</label>
                            <input type="file" 
                                   class="form-control" 
                                   name="file" 
                                   accept=".xlsx,.xls,.csv"
                                   required>
                            <small class="text-muted">Support: .xlsx, .xls, .csv (Max: 32MB)</small>
                        </div>

                        <!-- Options -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="replace_existing" value="1" id="replace_existing">
                                <label class="form-check-label" for="replace_existing">
                                    <strong>Replace Existing Data</strong>
                                    <div class="text-warning small">⚠️ Akan menghapus semua data responden dan dataset yang ada</div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i> Mulai Import
                            </button>
                        </div>
                    </form>

                    <!-- Instructions -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Petunjuk:</h6>
                        <ul class="mb-0">
                            <li>File harus dalam format Excel (.xlsx, .xls) atau CSV (.csv)</li>
                            <li>Pastikan kolom wajib ada: nama, durasi_penggunaan, frekuensi_akses, label_intensitas</li>
                            <li>Kolom opsional: nim, jurusan, semester, perhatian_konten, penghayatan</li>
                            <li>Proses import mungkin membutuhkan waktu untuk file besar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection