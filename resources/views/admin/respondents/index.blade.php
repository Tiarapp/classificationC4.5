@extends('admin.layout')

@section('title', 'Data Responden')
@section('page-title', 'Data Responden')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-people me-2"></i>
                    Daftar Responden Mahasiswa
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-custom" onclick="exportRespondents()">
                        <i class="bi bi-file-earmark-excel me-2"></i>
                        Export Excel
                    </button>
                    <a href="{{ route('admin.respondents.create') }}" class="btn btn-primary btn-custom">
                        <i class="bi bi-plus-lg me-2"></i>
                        Tambah Responden
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($respondents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Nama</th>
                                    <th width="15%">NIM</th>
                                    <th width="25%">Jurusan</th>
                                    <th width="10%">Semester</th>
                                    <th width="15%">Dataset</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($respondents as $index => $respondent)
                                    <tr>
                                        <td>{{ $respondents->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $respondent->nama }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $respondent->nim }}</span>
                                        </td>
                                        <td>{{ $respondent->jurusan }}</td>
                                        <td>
                                            <span class="badge bg-info">Semester {{ $respondent->semester }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $respondent->datasets_count > 0 ? 'bg-success' : 'bg-warning' }}">
                                                {{ $respondent->datasets_count }} data
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.respondents.show', $respondent) }}">
                                                            <i class="bi bi-eye me-2"></i>Detail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.respondents.edit', $respondent) }}">
                                                            <i class="bi bi-pencil me-2"></i>Edit
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.respondents.destroy', $respondent) }}" method="POST" 
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
                    @if($respondents->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $respondents->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada data responden</h5>
                        <p class="text-muted">Silakan tambahkan data responden mahasiswa untuk memulai penelitian.</p>
                        <a href="{{ route('admin.respondents.create') }}" class="btn btn-primary btn-custom">
                            <i class="bi bi-plus-lg me-2"></i>
                            Tambah Responden Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="bi bi-people fs-1 mb-2"></i>
                <h4>{{ $respondents->total() }}</h4>
                <small>Total Responden</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body text-center">
                <i class="bi bi-database fs-1 mb-2"></i>
                <h4>{{ $respondents->sum('datasets_count') }}</h4>
                <small>Total Dataset</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body text-center">
                <i class="bi bi-mortarboard fs-1 mb-2"></i>
                <h4>{{ $respondents->pluck('jurusan')->unique()->count() }}</h4>
                <small>Program Studi</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card info">
            <div class="card-body text-center">
                <i class="bi bi-bookmark fs-1 mb-2"></i>
                <h4>{{ $respondents->avg('semester') ? number_format($respondents->avg('semester'), 1) : '0' }}</h4>
                <small>Rata-rata Semester</small>
            </div>
        </div>
    </div>
</div>
<script>
async function exportRespondents() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    try {
        // Show loading state
        button.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Exporting...';
        button.disabled = true;
        
        // Fetch with authentication
        const response = await fetch('{{ route('admin.respondents.export') }}', {
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
        let filename = 'data-responden-' + new Date().toISOString().split('T')[0] + '.xlsx';
        
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
                    Data responden berhasil diexport!
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

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush