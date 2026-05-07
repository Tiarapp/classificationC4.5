@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Responden
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ $statistics['total_respondents'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Data Training
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ $statistics['training_data_count'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-database fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Akurasi Model
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ $modelAccuracy ? number_format($modelAccuracy * 100, 2) . '%' : 'Belum Ada' }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cpu fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Prediksi
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            {{ $statistics['total_predictions'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Classification Distribution Chart -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-pie-chart me-2"></i>
                    Distribusi Klasifikasi Intensitas
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="classificationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Duration Usage Chart -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-bar-chart me-2"></i>
                    Durasi Penggunaan TikTok
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="durationChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Frequency Access Chart -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-graph-down me-2"></i>
                    Frekuensi Akses Harian
                </h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="frequencyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-clock me-2"></i>
                    Aktivitas Terbaru
                </h6>
            </div>
            <div class="card-body">
                @if($latestTrainingSession)
                    <div class="mb-3">
                        <h6 class="text-primary">Model Training Terakhir</h6>
                        <p class="mb-1">
                            <strong>Status:</strong> 
                            <span class="badge {{ $latestTrainingSession->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($latestTrainingSession->status) }}
                            </span>
                        </p>
                        <p class="mb-1">
                            <strong>Akurasi:</strong> {{ $latestTrainingSession->accuracy_percentage }}
                        </p>
                        <p class="mb-0 text-muted">
                            <small>{{ $latestTrainingSession->created_at->diffForHumans() }}</small>
                        </p>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada training yang dilakukan</p>
                        <button class="btn btn-primary btn-custom" onclick="alert('Training akan segera tersedia!')">
                            <i class="bi bi-play-fill me-2"></i>
                            Mulai Training
                        </button>
                    </div>
                @endif
                
                <hr>
                
                <div>
                    <h6 class="text-primary">Statistik Dataset</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="py-2">
                                <div class="h6 mb-0">{{ $statistics['total_datasets'] }}</div>
                                <small class="text-muted">Total Data</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="py-2">
                                <div class="h6 mb-0">{{ $statistics['training_data_count'] }}</div>
                                <small class="text-muted">Training</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="py-2">
                                <div class="h6 mb-0">{{ $statistics['testing_data_count'] }}</div>
                                <small class="text-muted">Testing</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightning me-2"></i>
                    Aksi Cepat
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.respondents.create') }}" class="btn btn-outline-primary btn-custom w-100 py-3">
                            <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                            Tambah Responden
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.datasets.create') }}" class="btn btn-outline-success btn-custom w-100 py-3">
                            <i class="bi bi-database-add fs-4 d-block mb-2"></i>
                            Input Dataset
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-warning btn-custom w-100 py-3" onclick="alert('Import Excel akan segera tersedia!')">
                            <i class="bi bi-file-earmark-excel fs-4 d-block mb-2"></i>
                            Import Excel
                        </button>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button class="btn btn-outline-info btn-custom w-100 py-3" onclick="alert('Prediksi akan segera tersedia!')">
                            <i class="bi bi-magic fs-4 d-block mb-2"></i>
                            Buat Prediksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Classification Distribution Chart
    const classificationData = @json($classificationDistribution);
    const classificationCtx = document.getElementById('classificationChart').getContext('2d');
    new Chart(classificationCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(classificationData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
            datasets: [{
                data: Object.values(classificationData),
                backgroundColor: [
                    '#36A2EB',
                    '#FF6384', 
                    '#FFCE56'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Duration Chart
    const durationData = @json($durasiDistribution);
    const durationCtx = document.getElementById('durationChart').getContext('2d');
    new Chart(durationCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(durationData),
            datasets: [{
                label: 'Jumlah Pengguna',
                data: Object.values(durationData),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Frequency Chart
    const frequencyData = @json($frekuensiDistribution);
    const frequencyCtx = document.getElementById('frequencyChart').getContext('2d');
    new Chart(frequencyCtx, {
        type: 'line',
        data: {
            labels: Object.keys(frequencyData),
            datasets: [{
                label: 'Frekuensi Akses',
                data: Object.values(frequencyData),
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Auto-refresh statistics every 30 seconds
    setInterval(function() {
        fetch('{{ route("admin.dashboard") }}/stats')
            .then(response => response.json())
            .then(data => {
                // Update stat cards if needed
                console.log('Statistics updated:', data);
            })
            .catch(error => console.error('Error updating stats:', error));
    }, 30000);
});
</script>
@endpush