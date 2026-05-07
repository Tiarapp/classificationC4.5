@extends('admin.layout')

@section('title', 'Detail Dataset')
@section('page-title', 'Detail Dataset Kuesioner')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-eye me-2"></i>
                    Detail Data Kuesioner TikTok
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.datasets.edit', $dataset) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil me-2"></i>
                        Edit Data
                    </a>
                    <a href="{{ route('admin.datasets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Informasi Responden -->
                    <div class="col-lg-6">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="m-0">
                                    <i class="bi bi-person me-2"></i>
                                    Informasi Responden
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%" class="fw-bold">Nama:</td>
                                        <td>{{ $dataset->respondent->nama }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">NIM:</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $dataset->respondent->nim }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Jurusan:</td>
                                        <td>{{ $dataset->respondent->jurusan }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Semester:</td>
                                        <td>
                                            <span class="badge bg-info">Semester {{ $dataset->respondent->semester }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total Dataset:</td>
                                        <td>
                                            <span class="badge bg-success">{{ $dataset->respondent->datasets->count() }} data</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Data Kuesioner -->
                    <div class="col-lg-6">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="m-0">
                                    <i class="bi bi-clipboard-data me-2"></i>
                                    Data Kuesioner TikTok
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%" class="fw-bold">Durasi Penggunaan:</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $dataset->durasi_penggunaan }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Frekuensi Akses:</td>
                                        <td>
                                            <span class="badge bg-info">{{ $dataset->frekuensi_akses }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Perhatian Konten:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $dataset->perhatian_konten ? '-fill' : '' }} text-warning me-1"></i>
                                                @endfor
                                                <span class="ms-2 badge bg-warning">{{ $dataset->perhatian_konten }}/5</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Penghayatan:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $dataset->penghayatan ? '-fill' : '' }} text-warning me-1"></i>
                                                @endfor
                                                <span class="ms-2 badge bg-warning">{{ $dataset->penghayatan }}/5</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Label Intensitas:</td>
                                        <td>
                                            <span class="badge fs-6 {{ 
                                                $dataset->label_intensitas == 'tinggi' ? 'bg-danger' : 
                                                ($dataset->label_intensitas == 'sedang' ? 'bg-warning text-dark' : 'bg-success') 
                                            }}">
                                                {{ strtoupper($dataset->label_intensitas) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metadata & Additional Info -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="m-0">
                                    <i class="bi bi-database me-2"></i>
                                    Metadata & Informasi Tambahan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold">Tipe Data:</td>
                                                <td>
                                                    <span class="badge {{ $dataset->is_training_data ? 'bg-primary' : 'bg-secondary' }} fs-6">
                                                        {{ $dataset->is_training_data ? 'TRAINING DATA' : 'TESTING DATA' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Tanggal Dibuat:</td>
                                                <td>{{ $dataset->created_at->format('d F Y, H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Terakhir Update:</td>
                                                <td>{{ $dataset->updated_at->format('d F Y, H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <h6><i class="bi bi-info-circle me-2"></i>Interpretasi Data</h6>
                                            <p class="mb-2">
                                                <strong>Tingkat Intensitas:</strong> 
                                                @if($dataset->label_intensitas == 'tinggi')
                                                    Responden menunjukkan penggunaan TikTok yang sangat aktif dengan durasi dan frekuensi tinggi.
                                                @elseif($dataset->label_intensitas == 'sedang')
                                                    Responden menggunakan TikTok secara moderat, tidak berlebihan namun cukup rutin.
                                                @else
                                                    Responden menggunakan TikTok dengan intensitas rendah, penggunaan minimal.
                                                @endif
                                            </p>
                                            <p class="mb-0">
                                                <strong>Fungsi dalam Model:</strong> 
                                                Data ini {{ $dataset->is_training_data ? 'akan digunakan untuk melatih algoritma C4.5' : 'akan digunakan untuk menguji akurasi model' }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('admin.datasets.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-list me-2"></i>
                                    Lihat Semua Dataset
                                </a>
                                <a href="{{ route('admin.datasets.create') }}" class="btn btn-success ms-2">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Tambah Dataset Baru
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('admin.datasets.edit', $dataset) }}" class="btn btn-warning">
                                    <i class="bi bi-pencil me-2"></i>
                                    Edit Data Ini
                                </a>
                                <form action="{{ route('admin.datasets.destroy', $dataset) }}" method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="d-inline ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash me-2"></i>
                                        Hapus Data
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection