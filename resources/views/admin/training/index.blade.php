@extends('admin.layout')

@section('title', 'Training Model')
@section('page-title', 'Training Model C4.5')

@push('styles')
<style>
    .dropdown {
        position: relative;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        z-index: 1000;
        min-width: 160px;
        padding: 0.5rem 0;
        margin: 0.125rem 0 0;
        font-size: 0.875rem;
        color: #212529;
        text-align: left;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
    }
    .dropdown-menu.show {
        display: block;
    }
    .dropdown-item {
        display: block;
        width: 100%;
        padding: 0.25rem 1rem;
        clear: both;
        font-weight: 400;
        color: #212529;
        text-align: inherit;
        text-decoration: none;
        white-space: nowrap;
        background-color: transparent;
        border: 0;
    }
    .dropdown-item:hover,
    .dropdown-item:focus {
        color: #1e2125;
        background-color: #e9ecef;
    }
    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
    }
    .dropdown-divider {
        height: 0;
        margin: 0.5rem 0;
        overflow: hidden;
        border-top: 1px solid rgba(0,0,0,.15);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-cpu me-2"></i>
                    Training Model C4.5 Algorithm
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-info btn-custom" data-bs-toggle="modal" data-bs-target="#featureImportanceModal">
                        <i class="bi bi-bar-chart me-2"></i>
                        Feature Importance
                    </button>
                    <a href="{{ route('admin.training.create') }}" class="btn btn-primary btn-custom">
                        <i class="bi bi-play-fill me-2"></i>
                        Mulai Training
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($recentSessions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Algoritma</th>
                                    <th width="15%">Akurasi</th>
                                    <th width="15%">Data Training</th>
                                    <th width="15%">Data Testing</th>
                                    <th width="15%">Waktu Training</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSessions as $index => $session)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $session->algorithm }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    <div class="progress-bar {{ $session->accuracy >= 0.8 ? 'bg-success' : ($session->accuracy >= 0.6 ? 'bg-warning' : 'bg-danger') }}" 
                                                         style="width: {{ $session->accuracy * 100 }}%"></div>
                                                </div>
                                                <span class="badge {{ $session->accuracy >= 0.8 ? 'bg-success' : ($session->accuracy >= 0.6 ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ number_format($session->accuracy * 100, 2) }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $session->train_data_count }} data</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $session->test_data_count }} data</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ number_format($session->training_time, 2) }}s</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $session->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" onclick="toggleTrainingDropdown(this)">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.training.show', $session) }}">
                                                            <i class="bi bi-eye me-2"></i>Detail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.training.export', $session) }}">
                                                            <i class="bi bi-download me-2"></i>Export
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.training.destroy', $session) }}" method="POST" 
                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi training ini?')" class="d-inline">
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
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-cpu fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada sesi training</h5>
                        <p class="text-muted">Silakan mulai training model C4.5 untuk mengklasifikasi penggunaan TikTok.</p>
                        <a href="{{ route('admin.training.create') }}" class="btn btn-primary">
                            <i class="bi bi-play-fill me-2"></i>
                            Mulai Training Pertama
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
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Training</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSessions }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cpu fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Akurasi Rata-rata</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($avgAccuracy * 100, 1) }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-target fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Model Terbaik</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $bestSession ? number_format($bestSession->accuracy * 100, 2) . '%' : 'N/A' }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-trophy fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Dataset</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Dataset::count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-database fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Importance Modal -->
<div class="modal fade" id="featureImportanceModal" tabindex="-1" aria-labelledby="featureImportanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="featureImportanceModalLabel">
                    <i class="bi bi-bar-chart me-2"></i>
                    Feature Importance Analysis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="featureImportanceContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Menganalisis pentingnya fitur...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Training dropdown functionality
    function toggleTrainingDropdown(button) {
        const dropdown = button.parentElement;
        const menu = dropdown.querySelector('.dropdown-menu');
        
        // Close all other open dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
            if (openMenu !== menu) {
                openMenu.classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        menu.classList.toggle('show');
        
        // Position dropdown menu
        const rect = button.getBoundingClientRect();
        menu.style.position = 'absolute';
        menu.style.top = (rect.height + 2) + 'px';
        menu.style.right = '0px';
        menu.style.left = 'auto';
        menu.style.zIndex = '1050';
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Load feature importance when modal is shown
    document.getElementById('featureImportanceModal').addEventListener('show.bs.modal', function () {
        loadFeatureImportance();
    });

    function loadFeatureImportance() {
        fetch('{{ route("admin.training.feature_importance") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFeatureImportance(data.data);
                } else {
                    document.getElementById('featureImportanceContent').innerHTML = 
                        '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('featureImportanceContent').innerHTML = 
                    '<div class="alert alert-danger">Error loading feature importance</div>';
            });
    }

    function displayFeatureImportance(importance) {
        const features = {
            'durasi_penggunaan': 'Durasi Penggunaan',
            'frekuensi_akses': 'Frekuensi Akses',
            'perhatian_konten': 'Perhatian Konten',
            'penghayatan': 'Penghayatan'
        };

        let html = '<div class="row">';
        
        Object.keys(importance).forEach(feature => {
            const percentage = (importance[feature] * 100).toFixed(2);
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">${features[feature]}</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: ${percentage}%" aria-valuenow="${percentage}" 
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">Information Gain: ${importance[feature].toFixed(4)}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        document.getElementById('featureImportanceContent').innerHTML = html;
    }
</script>
@endpush