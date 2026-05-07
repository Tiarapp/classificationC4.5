@extends('admin.layout')

@section('title', 'Riwayat Prediksi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">📊 Riwayat Prediksi</h4>
                    <div>
                        <a href="{{ route('admin.prediction.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Prediksi Baru
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="exportPredictions()">
                            <i class="fas fa-download"></i> Export Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <form method="GET" class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="end_date" 
                                   value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filter Prediksi</label>
                            <select class="form-control" name="prediction_filter">
                                <option value="">Semua Prediksi</option>
                                <option value="RENDAH" {{ request('prediction_filter') == 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                                <option value="SEDANG" {{ request('prediction_filter') == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                                <option value="TINGGI" {{ request('prediction_filter') == 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.prediction.history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['total'] }}</h3>
                            <p class="mb-0">Total Prediksi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['by_label']['RENDAH'] }}</h3>
                            <p class="mb-0">Intensitas Rendah</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['by_label']['SEDANG'] }}</h3>
                            <p class="mb-0">Intensitas Sedang</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['by_label']['TINGGI'] }}</h3>
                            <p class="mb-0">Intensitas Tinggi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Predictions Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Prediksi ({{ $predictions->total() }} hasil)</h5>
                </div>
                <div class="card-body">
                    @if($predictions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Nama</th>
                                        <th>Input Data</th>
                                        <th>Prediksi</th>
                                        <th>Confidence</th>
                                        <th>Model</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($predictions as $prediction)
                                    <tr>
                                        <td>
                                            <code>#{{ $prediction->id }}</code>
                                        </td>
                                        <td>
                                            <small>{{ $prediction->formatted_date }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $prediction->nama ?? 'Anonim' }}</strong>
                                            @if($prediction->notes)
                                                <br><small class="text-muted">{{ Str::limit($prediction->notes, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                <strong>Durasi:</strong> {{ $prediction->durasi_penggunaan }}<br>
                                                <strong>Frekuensi:</strong> {{ $prediction->frekuensi_akses }}<br>
                                                <strong>Perhatian:</strong> {{ $prediction->perhatian_konten }}<br>
                                                <strong>Penghayatan:</strong> {{ $prediction->penghayatan }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($prediction->predicted_label == 'RENDAH')
                                                <span class="badge bg-success">{{ $prediction->predicted_label }}</span>
                                            @elseif($prediction->predicted_label == 'SEDANG')
                                                <span class="badge bg-warning">{{ $prediction->predicted_label }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ $prediction->predicted_label }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($prediction->confidence_percentage)
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" 
                                                         role="progressbar" 
                                                         style="width: {{ $prediction->confidence_percentage }}%">
                                                        {{ $prediction->confidence_percentage }}%
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($prediction->trainingSession)
                                                <small>
                                                    {{ $prediction->model_type }}<br>
                                                    <strong>{{ $prediction->model_accuracy }}%</strong>
                                                </small>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.prediction.show', $prediction->id) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deletePrediction({{ $prediction->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $predictions->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5>Belum Ada Prediksi Tersimpan</h5>
                            <p class="text-muted">Mulai buat prediksi dan aktifkan opsi "Simpan Hasil" untuk melihat riwayat</p>
                            <a href="{{ route('admin.prediction.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Prediksi Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deletePrediction(id) {
    if (confirm('Yakin ingin menghapus hasil prediksi ini?')) {
        fetch(`/admin/prediction/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat menghapus data');
            console.error('Error:', error);
        });
    }
}

function exportPredictions() {
    // Get current filter parameters
    const params = new URLSearchParams();
    
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const predictionFilter = document.querySelector('select[name="prediction_filter"]').value;
    
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (predictionFilter) params.append('prediction_filter', predictionFilter);
    
    // Create download link
    const exportUrl = '{{ route("admin.prediction.export") }}' + (params.toString() ? '?' + params.toString() : '');
    
    // Trigger download
    window.open(exportUrl, '_blank');
}
</script>
@endpush