@extends('admin.layout')

@section('title', 'Tambah Dataset')
@section('page-title', 'Tambah Dataset Kuesioner')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-plus-lg me-2"></i>
                    Tambah Data Kuesioner TikTok
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.datasets.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Pilih Responden -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="respondent_id" class="form-label fw-bold">
                                    <i class="bi bi-person me-1"></i>
                                    Pilih Responden <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('respondent_id') is-invalid @enderror" 
                                        id="respondent_id" name="respondent_id" required>
                                    <option value="">-- Pilih Responden --</option>
                                    @foreach($respondents as $respondent)
                                        <option value="{{ $respondent->id }}" {{ old('respondent_id') == $respondent->id ? 'selected' : '' }}>
                                            {{ $respondent->nama }} - {{ $respondent->nim }} ({{ $respondent->jurusan }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('respondent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Durasi Penggunaan -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="durasi_penggunaan" class="form-label fw-bold">
                                    <i class="bi bi-clock me-1"></i>
                                    Durasi Penggunaan per Hari <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('durasi_penggunaan') is-invalid @enderror" 
                                        id="durasi_penggunaan" name="durasi_penggunaan" required>
                                    <option value="">-- Pilih Durasi --</option>
                                    @foreach($dataset->getDurasiOptions() as $durasi)
                                        <option value="{{ $durasi }}" {{ old('durasi_penggunaan') == $durasi ? 'selected' : '' }}>
                                            {{ $durasi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('durasi_penggunaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Frekuensi Akses -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="frekuensi_akses" class="form-label fw-bold">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Frekuensi Akses per Hari <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('frekuensi_akses') is-invalid @enderror" 
                                        id="frekuensi_akses" name="frekuensi_akses" required>
                                    <option value="">-- Pilih Frekuensi --</option>
                                    @foreach($dataset->getFrekuensiOptions() as $frekuensi)
                                        <option value="{{ $frekuensi }}" {{ old('frekuensi_akses') == $frekuensi ? 'selected' : '' }}>
                                            {{ $frekuensi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('frekuensi_akses')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Perhatian Konten -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="perhatian_konten" class="form-label fw-bold">
                                    <i class="bi bi-eye me-1"></i>
                                    Rating Perhatian terhadap Konten <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('perhatian_konten') is-invalid @enderror" 
                                        id="perhatian_konten" name="perhatian_konten" required>
                                    <option value="">-- Pilih Rating --</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('perhatian_konten') == $i ? 'selected' : '' }}>
                                            {{ $i }} - {{ $i == 1 ? 'Sangat Rendah' : ($i == 2 ? 'Rendah' : ($i == 3 ? 'Sedang' : ($i == 4 ? 'Tinggi' : 'Sangat Tinggi'))) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('perhatian_konten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Penghayatan -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="penghayatan" class="form-label fw-bold">
                                    <i class="bi bi-heart me-1"></i>
                                    Rating Penghayatan Emosional <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('penghayatan') is-invalid @enderror" 
                                        id="penghayatan" name="penghayatan" required>
                                    <option value="">-- Pilih Rating --</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('penghayatan') == $i ? 'selected' : '' }}>
                                            {{ $i }} - {{ $i == 1 ? 'Sangat Rendah' : ($i == 2 ? 'Rendah' : ($i == 3 ? 'Sedang' : ($i == 4 ? 'Tinggi' : 'Sangat Tinggi'))) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('penghayatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Label Intensitas -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="label_intensitas" class="form-label fw-bold">
                                    <i class="bi bi-tag me-1"></i>
                                    Label Intensitas Penggunaan <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('label_intensitas') is-invalid @enderror" 
                                        id="label_intensitas" name="label_intensitas" required>
                                    <option value="">-- Pilih Label --</option>
                                    @foreach($dataset->getLabelOptions() as $label)
                                        <option value="{{ $label }}" {{ old('label_intensitas') == $label ? 'selected' : '' }}>
                                            {{ ucfirst($label) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('label_intensitas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Data Type -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-database me-1"></i>
                                    Tipe Data
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_training_data" 
                                           name="is_training_data" value="1" {{ old('is_training_data') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_training_data">
                                        Gunakan sebagai data training (default: testing data)
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Data training digunakan untuk melatih model C4.5, sedangkan data testing untuk evaluasi akurasi.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <hr class="my-4">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.datasets.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    Simpan Data
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Information Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="m-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Petunjuk Pengisian Form
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Durasi Penggunaan:</h6>
                        <ul class="small">
                            <li><strong>≤1 jam:</strong> Penggunaan minimal, sesekali saja</li>
                            <li><strong>1-3 jam:</strong> Penggunaan sedang, beberapa kali sehari</li>
                            <li><strong>3-5 jam:</strong> Penggunaan tinggi, sering membuka TikTok</li>
                            <li><strong>>5 jam:</strong> Penggunaan sangat tinggi, hampir sepanjang hari</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Rating 1-5:</h6>
                        <ul class="small">
                            <li><strong>1:</strong> Sangat Rendah</li>
                            <li><strong>2:</strong> Rendah</li>
                            <li><strong>3:</strong> Sedang</li>
                            <li><strong>4:</strong> Tinggi</li>
                            <li><strong>5:</strong> Sangat Tinggi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection