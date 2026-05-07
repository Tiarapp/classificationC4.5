@extends('admin.layout')

@section('title', 'Prediksi Intensitas Penggunaan TikTok')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">🔮 Prediksi Intensitas Penggunaan TikTok</h1>
            <p class="text-muted mb-4">
                Selamat datang, <strong>{{ Auth::user()->name }}</strong>
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Prediction Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-magic"></i> Form Prediksi
                    </h6>
                </div>
                <div class="card-body">
                    <form id="predictionForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        
                        <!-- Model Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-brain"></i> Pilih Model
                            </label>
                            <select name="model_id" id="model_id" class="form-select" required>
                                <option value="">-- Pilih Model Terbaik --</option>
                                @if($bestModel)
                                    <option value="{{ $bestModel->id }}" selected>
                                        🏆 Best Model - {{ $bestModel->algorithm }} 
                                        (Accuracy: {{ number_format($bestModel->accuracy * 100, 2) }}%)
                                    </option>
                                @endif
                                @foreach($availableModels as $model)
                                    @if(!$bestModel || $model->id !== $bestModel->id)
                                        <option value="{{ $model->id }}">
                                            {{ $model->algorithm }} - 
                                            {{ number_format($model->accuracy * 100, 2) }}% - 
                                            {{ $model->created_at->format('d/m/Y H:i') }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text">Pilih model yang telah dilatih untuk melakukan prediksi</div>
                        </div>

                        <!-- Input Parameters -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="durasi_penggunaan" class="form-label fw-bold">
                                    <i class="fas fa-clock"></i> Durasi Penggunaan
                                </label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="durasi_penggunaan" 
                                           name="durasi_penggunaan" min="1" max="10" value="5" required>
                                    <span class="input-group-text" id="durasi_value">5</span>
                                </div>
                                <div class="form-text">Skala 1-10: Berapa lama anda menggunakan TikTok per hari</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="frekuensi_akses" class="form-label fw-bold">
                                    <i class="fas fa-refresh"></i> Frekuensi Akses
                                </label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="frekuensi_akses" 
                                           name="frekuensi_akses" min="1" max="10" value="5" required>
                                    <span class="input-group-text" id="frekuensi_value">5</span>
                                </div>
                                <div class="form-text">Skala 1-10: Seberapa sering anda membuka aplikasi TikTok</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="perhatian_konten" class="form-label fw-bold">
                                    <i class="fas fa-eye"></i> Perhatian Konten
                                </label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="perhatian_konten" 
                                           name="perhatian_konten" min="1" max="10" value="5" required>
                                    <span class="input-group-text" id="perhatian_value">5</span>
                                </div>
                                <div class="form-text">Skala 1-10: Seberapa fokus anda memperhatikan konten</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="penghayatan" class="form-label fw-bold">
                                    <i class="fas fa-heart"></i> Penghayatan
                                </label>
                                <div class="input-group">
                                    <input type="range" class="form-range" id="penghayatan" 
                                           name="penghayatan" min="1" max="10" value="5" required>
                                    <span class="input-group-text" id="penghayatan_value">5</span>
                                </div>
                                <div class="form-text">Skala 1-10: Seberapa dalam anda menghayati konten</div>
                            </div>
                        </div>

                        <!-- Save Prediction Section -->
                        <div class="card bg-light mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-save"></i> Simpan Hasil Prediksi
                                </h6>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="save_prediction" name="save_prediction">
                                    <label class="form-check-label fw-bold" for="save_prediction">
                                        Simpan hasil prediksi ke riwayat
                                    </label>
                                    <div class="form-text">Aktifkan untuk menyimpan hasil prediksi ini ke database</div>
                                </div>
                                
                                <div id="save_options" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama" class="form-label">
                                                <i class="fas fa-user"></i> Nama Responden (Opsional)
                                            </label>
                                            <input type="text" class="form-control" id="nama" name="nama" 
                                                   placeholder="Masukkan nama responden...">
                                            <div class="form-text">Kosongkan jika ingin anonim</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="notes" class="form-label">
                                                <i class="fas fa-sticky-note"></i> Catatan (Opsional)
                                            </label>
                                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                                      placeholder="Tambahkan catatan atau keterangan..."></textarea>
                                            <div class="form-text">Catatan tambahan untuk prediksi ini</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-4">
                            <div>
                                <a href="{{ route('admin.prediction.history') }}" class="btn btn-outline-info">
                                    <i class="fas fa-history"></i> Riwayat Prediksi
                                </a>
                                <a href="{{ route('admin.training.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Training
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary" id="predictButton">
                                    <i class="fas fa-magic"></i> Prediksi Sekarang
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Prediction Results -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Hasil Prediksi
                    </h6>
                </div>
                <div class="card-body">
                    <div id="predictionResults" class="text-center text-muted">
                        <i class="fas fa-magic fa-3x mb-3"></i>
                        <p>Masukkan data dan klik prediksi untuk melihat hasil</p>
                    </div>
                </div>
            </div>

            <!-- Model Info -->
            @if($bestModel)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Model Terbaik
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary">{{ number_format($bestModel->accuracy * 100, 1) }}%</h5>
                                <small class="text-muted">Akurasi</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success">{{ $bestModel->algorithm }}</h5>
                            <small class="text-muted">Algoritma</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <strong>{{ $bestModel->train_data_count }}</strong>
                            <br><small class="text-muted">Training Data</small>
                        </div>
                        <div class="col-6">
                            <strong>{{ $bestModel->test_data_count }}</strong>
                            <br><small class="text-muted">Testing Data</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Simplified without modal - using button state feedback -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update range values
    document.getElementById('durasi_penggunaan').addEventListener('input', function() {
        document.getElementById('durasi_value').textContent = this.value;
    });
    
    document.getElementById('frekuensi_akses').addEventListener('input', function() {
        document.getElementById('frekuensi_value').textContent = this.value;
    });
    
    document.getElementById('perhatian_konten').addEventListener('input', function() {
        document.getElementById('perhatian_value').textContent = this.value;
    });
    
    document.getElementById('penghayatan').addEventListener('input', function() {
        document.getElementById('penghayatan_value').textContent = this.value;
    });

    // Handle save prediction toggle
    document.getElementById('save_prediction').addEventListener('change', function() {
        const saveOptions = document.getElementById('save_options');
        if (this.checked) {
            saveOptions.style.display = 'block';
        } else {
            saveOptions.style.display = 'none';
        }
    });

    // Handle prediction - simplified without modal
    document.getElementById('predictionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        makePrediction();
    });

    function makePrediction() {
        const form = document.getElementById('predictionForm');
        const formData = new FormData(form);
        const predictButton = document.getElementById('predictButton');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Prevent double submission
        if (predictButton.disabled) return;
        
        // Add CSRF token
        formData.append('_token', csrfToken);
        
        // Update button state
        predictButton.disabled = true;
        predictButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        
        // Timeout for hanging requests
        const timeoutId = setTimeout(() => {
            predictButton.disabled = false;
            predictButton.innerHTML = 'Prediksi Sekarang';
            displayPredictionResult({
                success: false,
                message: 'Request timeout. Silakan coba lagi.'
            });
        }, 15000);
        
        fetch('{{ route("admin.prediction.predict") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            displayPredictionResult(data);
        })
        .catch(error => {
            console.error('Prediction error:', error);
            displayPredictionResult({
                success: false,
                message: 'Error: ' + error.message
            });
        })
        .finally(() => {
            clearTimeout(timeoutId);
            predictButton.disabled = false;
            predictButton.innerHTML = 'Prediksi Sekarang';
        });
    }
    document.getElementById('durasi_penggunaan').addEventListener('input', function() {
        document.getElementById('durasi_value').textContent = this.value;
    });
    
    document.getElementById('frekuensi_akses').addEventListener('input', function() {
        document.getElementById('frekuensi_value').textContent = this.value;
    });
    
    document.getElementById('perhatian_konten').addEventListener('input', function() {
        document.getElementById('perhatian_value').textContent = this.value;
    });
    
    document.getElementById('penghayatan').addEventListener('input', function() {
        document.getElementById('penghayatan_value').textContent = this.value;
    });

    function displayPredictionResult(data) {
        const resultContainer = document.getElementById('predictionResults');
        
        if (data.success) {
            const prediction = data.prediction;
            const intensityClass = prediction.toLowerCase();
            const intensityIcon = {
                'rendah': 'fas fa-battery-quarter text-success',
                'sedang': 'fas fa-battery-half text-warning', 
                'tinggi': 'fas fa-battery-full text-danger'
            };
            
            let savedInfo = '';
            if (data.saved) {
                savedInfo = `
                    <div class="alert alert-success mt-3 p-2">
                        <i class="fas fa-check-circle"></i> Prediksi tersimpan dengan ID #${data.saved_id}
                        <a href="/admin/prediction/${data.saved_id}" class="btn btn-sm btn-outline-success ms-2">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                `;
            }
            
            resultContainer.innerHTML = `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="${intensityIcon[intensityClass]} fa-4x mb-2"></i>
                        <h4 class="text-${intensityClass === 'rendah' ? 'success' : intensityClass === 'sedang' ? 'warning' : 'danger'}">
                            ${prediction.toUpperCase()}
                        </h4>
                        <p class="text-muted">Intensitas Penggunaan TikTok</p>
                        
                        <div class="badge bg-info">
                            Confidence: ${(data.confidence * 100).toFixed(1)}%
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <h6 class="mb-2">Data Input:</h6>
                        <div class="row text-start">
                            <div class="col-6"><small>Durasi:</small></div>
                            <div class="col-6"><strong>${data.input_data.durasi_penggunaan}</strong></div>
                            <div class="col-6"><small>Frekuensi:</small></div>
                            <div class="col-6"><strong>${data.input_data.frekuensi_akses}</strong></div>
                            <div class="col-6"><small>Perhatian:</small></div>
                            <div class="col-6"><strong>${data.input_data.perhatian_konten}</strong></div>
                            <div class="col-6"><small>Penghayatan:</small></div>
                            <div class="col-6"><strong>${data.input_data.penghayatan}</strong></div>
                        </div>
                        
                        ${data.categorical_data ? `
                            <div class="mt-2">
                                <small class="text-muted">
                                    Kategori: ${data.categorical_data.durasi_penggunaan}, ${data.categorical_data.frekuensi_akses}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="border-top pt-3 mt-3">
                        <small class="text-muted">
                            Model: ${data.model_info.algorithm}<br>
                            Akurasi: ${(data.model_info.accuracy * 100).toFixed(1)}%
                        </small>
                    </div>
                    
                    ${savedInfo}
                </div>
            `;
        } else {
            resultContainer.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5>Prediksi Gagal</h5>
                    <p class="text-muted">${data.message}</p>
                </div>
            `;
        }
    }
});
</script>
@endsection