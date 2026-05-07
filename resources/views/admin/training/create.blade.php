@extends('admin.layout')

@section('title', 'Mulai Training')
@section('page-title', 'Mulai Training Model C4.5')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-play-fill me-2"></i>
                    Konfigurasi Training Model
                </h6>
            </div>
            <div class="card-body">
                <form id="trainingForm">
                    @csrf
                    
                    <div class="row">
                        <!-- Training Parameters -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="train_ratio" class="form-label fw-bold">
                                    <i class="bi bi-pie-chart me-1"></i>
                                    Rasio Data Training
                                </label>
                                <input type="range" class="form-range" id="train_ratio" name="train_ratio" 
                                       min="0.1" max="0.9" step="0.1" value="0.7">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">10%</small>
                                    <small id="trainRatioValue" class="text-primary fw-bold">70%</small>
                                    <small class="text-muted">90%</small>
                                </div>
                                <small class="form-text text-muted">
                                    Persentase data yang digunakan untuk training (sisanya untuk testing)
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_depth" class="form-label fw-bold">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    Kedalaman Maksimal Tree
                                </label>
                                <input type="number" class="form-control" id="max_depth" name="max_depth" 
                                       value="10" min="1" max="20">
                                <small class="form-text text-muted">
                                    Batas kedalaman decision tree (1-20)
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_samples_leaf" class="form-label fw-bold">
                                    <i class="bi bi-leaf me-1"></i>
                                    Minimum Sampel per Leaf
                                </label>
                                <input type="number" class="form-control" id="min_samples_leaf" name="min_samples_leaf" 
                                       value="2" min="1" max="20">
                                <small class="form-text text-muted">
                                    Jumlah minimal data untuk membuat leaf node
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-scissors me-1"></i>
                                    Opsi Pruning
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable_pruning" 
                                           name="enable_pruning" checked>
                                    <label class="form-check-label" for="enable_pruning">
                                        Aktifkan Post-Pruning
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Mengurangi overfitting dengan memangkas cabang yang tidak perlu
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Training Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.training.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-info" onclick="startCrossValidation()">
                                        <i class="bi bi-shuffle me-2"></i>
                                        Cross Validation
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="trainButton">
                                        <i class="bi bi-play-fill me-2"></i>
                                        Mulai Training
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Dataset Overview -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="bi bi-database me-2"></i>
                    Overview Dataset
                </h6>
            </div>
            <div class="card-body">
                @php
                    $totalDatasets = \App\Models\Dataset::count();
                    $classDistribution = \App\Models\Dataset::select('label_intensitas')
                        ->selectRaw('count(*) as count')
                        ->groupBy('label_intensitas')
                        ->get();
                    $trainingData = \App\Models\Dataset::where('is_training_data', true)->count();
                    $testingData = \App\Models\Dataset::where('is_training_data', false)->count();
                @endphp

                <div class="mb-3">
                    <h6 class="text-primary">Total Dataset: {{ $totalDatasets }}</h6>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $totalDatasets > 0 ? ($trainingData / $totalDatasets) * 100 : 0 }}%">
                            Training ({{ $trainingData }})
                        </div>
                        <div class="progress-bar bg-secondary" role="progressbar" 
                             style="width: {{ $totalDatasets > 0 ? ($testingData / $totalDatasets) * 100 : 0 }}%">
                            Testing ({{ $testingData }})
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-primary">Distribusi Kelas:</h6>
                    @foreach($classDistribution as $class)
                        @php
                            $percentage = $totalDatasets > 0 ? ($class->count / $totalDatasets) * 100 : 0;
                            $badgeClass = $class->label_intensitas == 'tinggi' ? 'bg-danger' : 
                                         ($class->label_intensitas == 'sedang' ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($class->label_intensitas) }}</span>
                            <div class="flex-grow-1 mx-2">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $badgeClass }}" role="progressbar" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">{{ $class->count }} ({{ number_format($percentage, 1) }}%)</small>
                        </div>
                    @endforeach
                </div>

                @if($totalDatasets < 10)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian!</strong> Minimum 10 data diperlukan untuk training yang efektif.
                        Saat ini hanya {{ $totalDatasets }} data tersedia.
                    </div>
                @endif
            </div>
        </div>

        <!-- Cross Validation Card -->
        <div class="card mt-3">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-shuffle me-2"></i>
                    Cross Validation
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="cv_k" class="form-label fw-bold">K-Fold:</label>
                    <select class="form-select" id="cv_k" name="cv_k">
                        <option value="3">3-Fold</option>
                        <option value="5" selected>5-Fold</option>
                        <option value="10">10-Fold</option>
                    </select>
                </div>
                <p class="text-muted small">
                    Cross validation memberikan estimasi performa model yang lebih robust 
                    dengan membagi data menjadi K bagian dan melakukan training K kali.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Training Progress Modal -->
<div class="modal fade" id="trainingProgressModal" tabindex="-1" aria-labelledby="trainingProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trainingProgressModalLabel">
                    <i class="bi bi-cpu me-2"></i>
                    Training Model C4.5
                </h5>
            </div>
            <div class="modal-body">
                <div id="trainingProgress">
                    <div class="text-center mb-4">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Sedang melatih model...</p>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
                <div id="trainingResult" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeModal">Tutup</button>
                <a href="{{ route('admin.training.index') }}" class="btn btn-primary" id="viewResults" style="display: none;">
                    Lihat Hasil Training
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Update train ratio display
    document.getElementById('train_ratio').addEventListener('input', function() {
        const value = (this.value * 100).toFixed(0);
        document.getElementById('trainRatioValue').textContent = value + '%';
    });

    // Handle training form submission
    document.getElementById('trainingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        startTraining();
    });

    function startTraining() {
        const formData = new FormData(document.getElementById('trainingForm'));
        const trainButton = document.getElementById('trainButton');
        
        // Convert checkbox value to proper boolean
        const enablePruning = document.getElementById('enable_pruning').checked;
        formData.set('enable_pruning', enablePruning ? '1' : '0');
        
        // Show progress modal
        const modal = new bootstrap.Modal(document.getElementById('trainingProgressModal'));
        modal.show();
        
        // Disable button
        trainButton.disabled = true;
        
        fetch('{{ route("admin.training.train") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            displayTrainingResult(data);
        })
        .catch(error => {
            displayTrainingResult({
                success: false,
                message: 'Error: ' + error.message
            });
        })
        .finally(() => {
            trainButton.disabled = false;
        });
    }

    function startCrossValidation() {
        const formData = new FormData();
        formData.append('k', document.getElementById('cv_k').value);
        formData.append('max_depth', document.getElementById('max_depth').value);
        formData.append('min_samples_leaf', document.getElementById('min_samples_leaf').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Show progress modal
        const modal = new bootstrap.Modal(document.getElementById('trainingProgressModal'));
        document.getElementById('trainingProgressModalLabel').innerHTML = 
            '<i class="bi bi-shuffle me-2"></i>Cross Validation';
        modal.show();
        
        fetch('{{ route("admin.training.cross_validate") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayCrossValidationResult(data);
        })
        .catch(error => {
            displayTrainingResult({
                success: false,
                message: 'Error: ' + error.message
            });
        });
    }

    function displayTrainingResult(data) {
        document.getElementById('trainingProgress').style.display = 'none';
        document.getElementById('trainingResult').style.display = 'block';
        document.getElementById('viewResults').style.display = 'inline-block';
        
        let html = '';
        if (data.success) {
            html = `
                <div class="alert alert-success">
                    <h5><i class="bi bi-check-circle me-2"></i>Training Berhasil!</h5>
                    <p>${data.message}</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h5 class="card-title text-success">Akurasi Model</h5>
                                <h2 class="text-success">${data.data.accuracy}%</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h5 class="card-title text-info">Waktu Training</h5>
                                <h2 class="text-info">${data.data.training_time}s</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Statistik Tree:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Nodes:</strong> ${data.data.tree_stats.total_nodes}</li>
                        <li><strong>Leaf Nodes:</strong> ${data.data.tree_stats.leaf_nodes}</li>
                        <li><strong>Max Depth:</strong> ${data.data.tree_stats.max_depth}</li>
                    </ul>
                </div>
                <div class="mt-3">
                    <h6>Data Split:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Data:</strong> ${data.data.data_split.total}</li>
                        <li><strong>Training Data:</strong> ${data.data.data_split.train}</li>
                        <li><strong>Testing Data:</strong> ${data.data.data_split.test}</li>
                    </ul>
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Training Gagal!</h5>
                    <p>${data.message}</p>
                </div>
            `;
        }
        
        document.getElementById('trainingResult').innerHTML = html;
    }

    function displayCrossValidationResult(data) {
        document.getElementById('trainingProgress').style.display = 'none';
        document.getElementById('trainingResult').style.display = 'block';
        
        let html = '';
        if (data.success) {
            html = `
                <div class="alert alert-info">
                    <h5><i class="bi bi-check-circle me-2"></i>Cross Validation Selesai!</h5>
                    <p>${data.message}</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h5 class="card-title text-primary">Rata-rata Akurasi</h5>
                                <h2 class="text-primary">${data.data.average_accuracy}%</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-secondary">
                            <div class="card-body text-center">
                                <h5 class="card-title text-secondary">Std. Deviasi</h5>
                                <h2 class="text-secondary">±${data.data.std_accuracy}%</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Hasil per Fold:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fold</th>
                                    <th>Training Size</th>
                                    <th>Test Size</th>
                                    <th>Akurasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.data.folds.map(fold => `
                                    <tr>
                                        <td>${fold.fold}</td>
                                        <td>${fold.train_size}</td>
                                        <td>${fold.test_size}</td>
                                        <td>${(fold.accuracy * 100).toFixed(2)}%</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Cross Validation Gagal!</h5>
                    <p>${data.message}</p>
                </div>
            `;
        }
        
        document.getElementById('trainingResult').innerHTML = html;
    }
</script>
@endpush