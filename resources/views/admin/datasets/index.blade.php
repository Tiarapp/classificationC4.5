@extends('admin.layout')

@section('title', 'Dataset Kuesioner')
@section('page-title', 'Dataset Kuesioner')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                    Data Kuesioner TikTok
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-custom" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload me-2"></i>
                        Import Excel
                    </button>
                    <button type="button" class="btn btn-info btn-custom" onclick="exportDatasets()">
                        <i class="bi bi-download me-2"></i>
                        Export Excel
                    </button>
                    <a href="{{ route('admin.datasets.create') }}" class="btn btn-primary btn-custom">
                        <i class="bi bi-plus-lg me-2"></i>
                        Tambah Data
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($datasets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Responden</th>
                                    <th width="12%">Durasi</th>
                                    <th width="12%">Frekuensi</th>
                                    <th width="10%">Perhatian</th>
                                    <th width="10%">Penghayatan</th>
                                    <th width="12%">Label</th>
                                    <th width="9%">Data</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datasets as $index => $dataset)
                                    <tr>
                                        <td>{{ $datasets->firstItem() + $index }}</td>
                                        <td>
                                            @if($dataset->respondent)
                                                <strong>{{ $dataset->respondent->nama }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $dataset->respondent->nim }}</small>
                                            @else
                                                <strong class="text-muted">Data Responden Tidak Ditemukan</strong>
                                                <br>
                                                <small class="text-danger">ID Dataset: {{ $dataset->id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $dataset->durasi_penggunaan }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $dataset->frekuensi_akses }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $dataset->perhatian_konten ? '-fill' : '' }} text-warning"></i>
                                                @endfor
                                                <small class="ms-1">({{ $dataset->perhatian_konten }})</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $dataset->penghayatan ? '-fill' : '' }} text-warning"></i>
                                                @endfor
                                                <small class="ms-1">({{ $dataset->penghayatan }})</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ 
                                                $dataset->label_intensitas == 'tinggi' ? 'bg-danger' : 
                                                ($dataset->label_intensitas == 'sedang' ? 'bg-warning' : 'bg-success') 
                                            }}">
                                                {{ ucfirst($dataset->label_intensitas) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $dataset->is_training_data ? 'bg-primary' : 'bg-secondary' }}">
                                                {{ $dataset->is_training_data ? 'Training' : 'Testing' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.datasets.show', $dataset) }}">
                                                            <i class="bi bi-eye me-2"></i>Detail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.datasets.edit', $dataset) }}">
                                                            <i class="bi bi-pencil me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.datasets.destroy', $dataset) }}" method="POST" 
                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i>Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($datasets->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $datasets->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-spreadsheet fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada data kuesioner</h5>
                        <p class="text-muted">Silakan tambahkan data kuesioner TikTok untuk memulai analisis.</p>
                        <a href="{{ route('admin.datasets.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>
                            Tambah Data Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mt-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dataset</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDatasets }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-file-earmark-spreadsheet fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Data Training</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $trainingData }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Data Testing</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $testingData }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clipboard-data fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Responden</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalResponden }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="bi bi-upload me-2"></i>
                    Import Data Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.datasets.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel:</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv (max 2MB)</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Format Excel:</strong> Pastikan file memiliki kolom: Responden ID, Durasi Penggunaan, Frekuensi Akses, Perhatian Konten, Penghayatan, Label Intensitas, Is Training Data
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-2"></i>
                        Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
async function exportDatasets() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    try {
        // Show loading state
        button.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Exporting...';
        button.disabled = true;
        
        // Fetch with authentication
        const response = await fetch('{{ route('admin.datasets.export') }}', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,*/*'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get filename from Content-Disposition header or generate one
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'data-kuesioner-' + new Date().toISOString().split('T')[0] + '.xlsx';
        
        if (contentDisposition && contentDisposition.includes('filename=')) {
            filename = contentDisposition.split('filename=')[1].replace(/"/g, '');
        }
        
        // Create blob and download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        
        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast-container position-fixed top-0 end-0 p-3';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto text-success">Success</strong>
                    <button type="button" class="btn-close" onclick="this.closest('.toast-container').remove()"></button>
                </div>
                <div class="toast-body">
                    Data kuesioner berhasil diexport!
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Auto remove toast after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
        
    } catch (error) {
        console.error('Export error:', error);
        
        // Show error message
        const toast = document.createElement('div');
        toast.className = 'toast-container position-fixed top-0 end-0 p-3';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto text-danger">Error</strong>
                    <button type="button" class="btn-close" onclick="this.closest('.toast-container').remove()"></button>
                </div>
                <div class="toast-body">
                    Gagal export data: ${error.message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
        
    } finally {
        // Reset button state
        button.innerHTML = originalText;
        button.disabled = false;
    }
}
</script>

@endsection