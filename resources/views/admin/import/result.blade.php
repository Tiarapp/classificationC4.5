@extends('admin.layout')

@section('title', 'Hasil Import')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Result Summary -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        @if($results['success'])
                            <i class="fas fa-check-circle text-success"></i> Import Berhasil!
                        @else
                            <i class="fas fa-exclamation-triangle text-warning"></i> Import Selesai dengan Peringatan
                        @endif
                    </h4>
                    <div>
                        <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('admin.import.upload') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload"></i> Import Lagi
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Stats -->
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h3>{{ $results['success_count'] }}</h3>
                                    <p class="mb-0">Data Berhasil</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-{{ $results['error_count'] > 0 ? 'danger' : 'secondary' }} text-white">
                                <div class="card-body">
                                    <h3>{{ $results['error_count'] }}</h3>
                                    <p class="mb-0">Data Error</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3>{{ $results['total_respondents'] }}</h3>
                                    <p class="mb-0">Total Responden</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h3>{{ $results['total_datasets'] }}</h3>
                                    <p class="mb-0">Total Dataset</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    @if($results['success_count'] > 0)
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check"></i> Import Berhasil!</h5>
                            <p class="mb-0">
                                <strong>{{ $results['success_count'] }}</strong> data kuesioner berhasil diimport ke dalam sistem.
                                @if($results['error_count'] > 0)
                                    Terdapat <strong>{{ $results['error_count'] }}</strong> baris yang mengalami error dan dilewati.
                                @endif
                            </p>
                        </div>
                    @endif

                    <!-- Error Details -->
                    @if($results['error_count'] > 0 && !empty($results['errors']))
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Daftar Error ({{ count($results['errors']) }} baris)</h5>
                            <p>Baris-baris berikut mengalami error dan tidak diimport:</p>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="10%">Baris</th>
                                            <th width="40%">Error</th>
                                            <th width="50%">Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice($results['errors'], 0, 20) as $error)
                                            <tr>
                                                <td class="text-center">{{ $error['row'] }}</td>
                                                <td><span class="badge bg-danger">{{ $error['error'] }}</span></td>
                                                <td>
                                                    <small class="text-muted">
                                                        @foreach($error['data'] as $key => $value)
                                                            <strong>{{ $key }}:</strong> {{ $value ?? 'null' }}{{ !$loop->last ? ', ' : '' }}
                                                        @endforeach
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                        
                                        @if(count($results['errors']) > 20)
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    ... dan {{ count($results['errors']) - 20 }} error lainnya
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <h6>💡 Tips Mengatasi Error:</h6>
                                <ul class="small mb-0">
                                    <li>Pastikan format durasi_penggunaan: <=1 jam, 1-3 jam, 3-5 jam, >5 jam</li>
                                    <li>Pastikan format frekuensi_akses: 1-2x, 3-5x, >5x</li>
                                    <li>Pastikan perhatian_konten dan penghayatan antara 1-5</li>
                                    <li>Pastikan label_intensitas: rendah, sedang, tinggi</li>
                                    <li>Download template Excel untuk format yang benar</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Data Distribution -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">📊 Distribusi Data Setelah Import</h5>
                </div>
                <div class="card-body">
                    <div id="distribution-stats">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Memuat distribusi data...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <h6>🎯 Langkah Selanjutnya</h6>
                    <p class="text-muted mb-3">Data sudah berhasil diimport. Apa yang ingin Anda lakukan selanjutnya?</p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('admin.training.index') }}" class="btn btn-success">
                            <i class="fas fa-cogs"></i> Train Model C4.5
                        </a>
                        <a href="{{ route('admin.prediction.index') }}" class="btn btn-primary">
                            <i class="fas fa-search"></i> Test Prediksi
                        </a>
                        <a href="{{ route('admin.respondents.index') }}" class="btn btn-info">
                            <i class="fas fa-users"></i> Lihat Data Responden
                        </a>
                        <a href="{{ route('admin.datasets.index') }}" class="btn btn-warning">
                            <i class="fas fa-database"></i> Lihat Dataset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDistributionStats();
});

async function loadDistributionStats() {
    try {
        const response = await fetch('{{ route("admin.import.history") }}');
        const data = await response.json();
        
        let statsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6>📈 Summary</h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">${data.total_respondents}</h4>
                                <small>Responden</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">${data.total_datasets}</h4>
                                <small>Dataset</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6>🎯 Distribusi Label Intensitas</h6>
        `;
        
        if (data.distribution) {
            const colors = {rendah: 'success', sedang: 'warning', tinggi: 'danger'};
            const labels = Object.keys(data.distribution);
            const total = data.total_datasets;
            
            for (const label of labels) {
                const count = data.distribution[label];
                const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                
                statsHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-${colors[label]} me-2">${label}</span>
                            <span>${count} data</span>
                        </div>
                        <div class="progress" style="width: 100px; height: 20px;">
                            <div class="progress-bar bg-${colors[label]}" role="progressbar" 
                                 style="width: ${percentage}%" aria-valuenow="${percentage}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                ${percentage}%
                            </div>
                        </div>
                    </div>
                `;
            }
        }
        
        statsHtml += '</div></div>';
        
        // Add recommendation
        statsHtml += `
            <div class="mt-4 p-3 bg-light rounded">
                <h6>💡 Rekomendasi</h6>
                <p class="mb-0 small">
                    Data sudah siap untuk training C4.5! 
                    Dengan ${data.total_datasets} dataset yang terdistribusi, 
                    Anda dapat melatih model klasifikasi yang lebih akurat.
                </p>
            </div>
        `;
        
        document.getElementById('distribution-stats').innerHTML = statsHtml;
        
    } catch (error) {
        console.error('Error loading distribution stats:', error);
        document.getElementById('distribution-stats').innerHTML = 
            '<div class="alert alert-danger">Error memuat statistik distribusi</div>';
    }
}
</script>
@endsection