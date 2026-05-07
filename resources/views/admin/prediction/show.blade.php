@extends('admin.layout')

@section('title', 'Detail Prediksi #' . $prediction->id)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">🔍 Detail Prediksi #{{ $prediction->id }}</h4>
                    <div>
                        <a href="{{ route('admin.prediction.history') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button class="btn btn-outline-danger btn-sm" onclick="deletePrediction({{ $prediction->id }})">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Prediction Result -->
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h5>🎯 Hasil Prediksi</h5>
                                    @if($prediction->predicted_label == 'RENDAH')
                                        <div class="display-4 text-success mb-2">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                        <h2 class="text-success">RENDAH</h2>
                                        <p class="text-muted">Intensitas penggunaan TikTok rendah</p>
                                    @elseif($prediction->predicted_label == 'SEDANG')
                                        <div class="display-4 text-warning mb-2">
                                            <i class="fas fa-minus"></i>
                                        </div>
                                        <h2 class="text-warning">SEDANG</h2>
                                        <p class="text-muted">Intensitas penggunaan TikTok sedang</p>
                                    @else
                                        <div class="display-4 text-danger mb-2">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                        <h2 class="text-danger">TINGGI</h2>
                                        <p class="text-muted">Intensitas penggunaan TikTok tinggi</p>
                                    @endif
                                    
                                    @if($prediction->confidence_percentage)
                                        <div class="mt-3">
                                            <h6>Tingkat Kepercayaan</h6>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar" 
                                                     role="progressbar" 
                                                     style="width: {{ $prediction->confidence_percentage }}%">
                                                    {{ $prediction->confidence_percentage }}%
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Input Data -->
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h5>📝 Data Input</h5>
                                    
                                    @if($prediction->nama)
                                        <div class="mb-3">
                                            <strong>Nama Responden:</strong>
                                            <br>{{ $prediction->nama }}
                                        </div>
                                    @endif

                                    <div class="mb-2">
                                        <strong>Durasi Penggunaan:</strong>
                                        <span class="badge bg-primary ms-2">{{ $prediction->durasi_penggunaan }}</span>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Frekuensi Akses:</strong>
                                        <span class="badge bg-primary ms-2">{{ $prediction->frekuensi_akses }}</span>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Perhatian Konten:</strong>
                                        <div class="d-flex align-items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $prediction->perhatian_konten)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2">{{ $prediction->perhatian_konten }}/5</span>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Penghayatan:</strong>
                                        <div class="d-flex align-items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $prediction->penghayatan)
                                                    <i class="fas fa-heart text-danger"></i>
                                                @else
                                                    <i class="far fa-heart text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2">{{ $prediction->penghayatan }}/5</span>
                                        </div>
                                    </div>

                                    @if($prediction->notes)
                                        <div class="mt-3">
                                            <strong>Catatan:</strong>
                                            <div class="border p-2 mt-1 bg-white rounded">
                                                {{ $prediction->notes }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Model Information -->
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h5>🤖 Informasi Model</h5>
                                    
                                    <div class="mb-2">
                                        <strong>Algoritma:</strong>
                                        <span class="badge bg-info ms-2">{{ $prediction->model_type }}</span>
                                    </div>

                                    @if($prediction->model_accuracy)
                                        <div class="mb-2">
                                            <strong>Akurasi Model:</strong>
                                            <span class="badge bg-success ms-2">{{ $prediction->model_accuracy }}%</span>
                                        </div>
                                    @endif

                                    @if($prediction->trainingSession)
                                        <div class="mb-2">
                                            <strong>Training Session:</strong>
                                            <br><small class="text-muted">#{{ $prediction->trainingSession->id }}</small>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Tanggal Training:</strong>
                                            <br><small class="text-muted">{{ $prediction->trainingSession->created_at->format('d M Y H:i') }}</small>
                                        </div>
                                    @endif

                                    <hr>
                                    <div class="mb-2">
                                        <strong>Tanggal Prediksi:</strong>
                                        <br><small class="text-muted">{{ $prediction->formatted_date }}</small>
                                    </div>

                                    @if($prediction->ip_address)
                                        <div class="mb-2">
                                            <strong>IP Address:</strong>
                                            <br><small class="text-muted">{{ $prediction->ip_address }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prediction Details (if available) -->
                    @if($prediction->prediction_details)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">🌳 Detail Keputusan Model</h6>
                                    </div>
                                    <div class="card-body">
                                        <pre class="bg-light p-3 rounded" style="font-size: 12px; max-height: 300px; overflow-y: auto;">{{ json_encode($prediction->prediction_details, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="mb-3">🎯 Aksi Lanjutan</h6>
                                    
                                    <a href="{{ route('admin.prediction.index') }}" class="btn btn-primary me-2">
                                        <i class="fas fa-plus"></i> Prediksi Baru
                                    </a>
                                    
                                    <button class="btn btn-outline-success me-2" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i> Copy Data
                                    </button>
                                    
                                    @if($prediction->trainingSession)
                                        <a href="{{ route('admin.training.index') }}" class="btn btn-outline-info me-2">
                                            <i class="fas fa-cogs"></i> Lihat Model
                                        </a>
                                    @endif
                                    
                                    <button class="btn btn-outline-warning" onclick="exportSingle()">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deletePrediction(id) {
    if (confirm('Yakin ingin menghapus hasil prediksi ini?\nData akan hilang permanen.')) {
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
                alert('Hasil prediksi berhasil dihapus');
                window.location.href = '{{ route("admin.prediction.history") }}';
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

function copyToClipboard() {
    const data = {
        id: {{ $prediction->id }},
        tanggal: '{{ $prediction->formatted_date }}',
        nama: '{{ $prediction->nama ?? "Anonim" }}',
        durasi: '{{ $prediction->durasi_penggunaan }}',
        frekuensi: '{{ $prediction->frekuensi_akses }}',
        perhatian_konten: {{ $prediction->perhatian_konten }},
        penghayatan: {{ $prediction->penghayatan }},
        prediksi: '{{ $prediction->predicted_label }}',
        confidence: '{{ $prediction->confidence_percentage }}%',
        model: '{{ $prediction->model_type }}',
        akurasi: '{{ $prediction->model_accuracy }}%'
    };
    
    navigator.clipboard.writeText(JSON.stringify(data, null, 2))
        .then(() => alert('Data berhasil disalin ke clipboard'))
        .catch(err => alert('Gagal menyalin data: ' + err));
}

function exportSingle() {
    const exportUrl = '{{ route("admin.prediction.export_single", $prediction->id) }}';
    window.open(exportUrl, '_blank');
}
</script>
@endpush