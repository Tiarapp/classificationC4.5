@extends('admin.layout')

@section('title', 'Tambah Responden')
@section('page-title', 'Tambah Responden')

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-person-plus me-2"></i>
                    Form Tambah Responden Mahasiswa
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.respondents.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Lengkap *</label>
                            <input type="text" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama') }}" 
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nim" class="form-label">NIM *</label>
                            <input type="text" 
                                   class="form-control @error('nim') is-invalid @enderror" 
                                   id="nim" 
                                   name="nim" 
                                   value="{{ old('nim') }}" 
                                   placeholder="Contoh: 2019001"
                                   required>
                            @error('nim')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="jurusan" class="form-label">Program Studi/Jurusan *</label>
                            <select class="form-control @error('jurusan') is-invalid @enderror" 
                                    id="jurusan" 
                                    name="jurusan" 
                                    required>
                                <option value="">-- Pilih Program Studi --</option>
                                <option value="Teknik Informatika" {{ old('jurusan') == 'Teknik Informatika' ? 'selected' : '' }}>
                                    Teknik Informatika
                                </option>
                                <option value="Sistem Informasi" {{ old('jurusan') == 'Sistem Informasi' ? 'selected' : '' }}>
                                    Sistem Informasi
                                </option>
                                <option value="Manajemen Informatika" {{ old('jurusan') == 'Manajemen Informatika' ? 'selected' : '' }}>
                                    Manajemen Informatika
                                </option>
                                <option value="Teknik Komputer" {{ old('jurusan') == 'Teknik Komputer' ? 'selected' : '' }}>
                                    Teknik Komputer
                                </option>
                                <option value="Teknologi Informasi" {{ old('jurusan') == 'Teknologi Informasi' ? 'selected' : '' }}>
                                    Teknologi Informasi
                                </option>
                            </select>
                            @error('jurusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-control @error('semester') is-invalid @enderror" 
                                    id="semester" 
                                    name="semester" 
                                    required>
                                <option value="">-- Pilih --</option>
                                @for($i = 1; $i <= 14; $i++)
                                    <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>
                                        Semester {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Catatan:</strong> Pastikan data yang dimasukkan benar karena akan digunakan untuk penelitian klasifikasi intensitas penggunaan TikTok.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.respondents.index') }}" class="btn btn-secondary btn-custom">
                            <i class="bi bi-arrow-left me-2"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-primary btn-custom">
                            <i class="bi bi-save me-2"></i>
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Input Guidelines Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightbulb me-2"></i>
                    Panduan Pengisian Data
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Format Data</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-2"></i> Nama: Gunakan nama lengkap sesuai KTM</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i> NIM: Nomor Induk Mahasiswa (angka)</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i> Jurusan: Pilih sesuai program studi</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i> Semester: Semester aktif saat penelitian</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Contoh Pengisian</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-arrow-right text-primary me-2"></i> Nama: Ahmad Rifai</li>
                            <li><i class="bi bi-arrow-right text-primary me-2"></i> NIM: 2019001</li>
                            <li><i class="bi bi-arrow-right text-primary me-2"></i> Jurusan: Teknik Informatika</li>
                            <li><i class="bi bi-arrow-right text-primary me-2"></i> Semester: 6</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-capitalize first letter of name
    $('#nama').on('input', function() {
        let value = $(this).val();
        if (value.length > 0) {
            $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
        }
    });

    // Format NIM input (numbers only)
    $('#nim').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Form validation feedback
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
        $(this).find('button[type="submit"]').prop('disabled', true);
    });
});
</script>
@endpush