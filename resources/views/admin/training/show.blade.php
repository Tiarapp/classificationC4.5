@extends('admin.layout')

@section('title', 'Detail Training Session')
@section('page-title', 'Detail Training Session')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.training.index') }}">Training</a></li>
                    <li class="breadcrumb-item active">Detail Session #{{ $session->id }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.training.export', $session) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-download me-1"></i>Export
                </a>
                <a href="{{ route('admin.training.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Session Summary -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-cpu text-primary fs-1"></i>
                <h6 class="card-title mt-2">Algorithm</h6>
                <span class="badge bg-primary fs-6">{{ $session->algorithm }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-bullseye text-success fs-1"></i>
                <h6 class="card-title mt-2">Accuracy</h6>
                <span class="badge {{ $session->accuracy >= 0.8 ? 'bg-success' : ($session->accuracy >= 0.6 ? 'bg-warning' : 'bg-danger') }} fs-6">
                    {{ number_format($session->accuracy * 100, 2) }}%
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-database text-info fs-1"></i>
                <h6 class="card-title mt-2">Data Split</h6>
                <small class="text-muted">
                    Train: <span class="badge bg-info">{{ $session->train_data_count }}</span><br>
                    Test: <span class="badge bg-secondary">{{ $session->test_data_count }}</span>
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning fs-1"></i>
                <h6 class="card-title mt-2">Training Time</h6>
                <span class="badge bg-warning text-dark fs-6">{{ number_format($session->training_time, 2) }}s</span>
            </div>
        </div>
    </div>
</div>

@if($modelData)
<!-- Single Data Prediction Test -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="bi bi-search me-2"></i>
                    Test Prediksi dengan Data Tunggal
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <form id="singlePredictionForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="durasi_penggunaan" class="form-label">Durasi Penggunaan</label>
                                    <select class="form-select" id="durasi_penggunaan" name="durasi_penggunaan" required>
                                        <option value="">Pilih durasi...</option>
                                        <option value="<=1 jam"><=1 jam</option>
                                        <option value="1-3 jam">1-3 jam</option>
                                        <option value="3-5 jam">3-5 jam</option>
                                        <option value=">5 jam">>5 jam</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="frekuensi_akses" class="form-label">Frekuensi Akses</label>
                                    <select class="form-select" id="frekuensi_akses" name="frekuensi_akses" required>
                                        <option value="">Pilih frekuensi...</option>
                                        <option value="1-2x">1-2x</option>
                                        <option value="3-5x">3-5x</option>
                                        <option value=">5x">>5x</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="perhatian_konten" class="form-label">Perhatian Konten (1-5)</label>
                                    <input type="number" class="form-control" id="perhatian_konten" name="perhatian_konten" 
                                           min="1" max="5" required placeholder="Masukkan nilai 1-5">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="penghayatan" class="form-label">Penghayatan (1-5)</label>
                                    <input type="number" class="form-control" id="penghayatan" name="penghayatan" 
                                           min="1" max="5" required placeholder="Masukkan nilai 1-5">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-play-circle me-2"></i>Jalankan Prediksi
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="clearPredictionForm()">
                                <i class="bi bi-x-circle me-2"></i>Reset
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-secondary mb-2">
                                <i class="bi bi-info-circle me-2"></i>Panduan Input
                            </h6>
                            <small class="text-muted">
                                <strong>Durasi Penggunaan:</strong> Berapa lama menggunakan TikTok per hari<br>
                                <strong>Frekuensi Akses:</strong> Seberapa sering membuka TikTok per hari<br>
                                <strong>Perhatian Konten:</strong> Tingkat fokus saat menonton (1-5)<br>
                                <strong>Penghayatan:</strong> Seberapa dalam memahami konten (1-5)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Prediction Result & Decision Path -->
<div class="row mb-4" id="predictionResultContainer" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Hasil Prediksi & Path Decision Tree
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-4 border rounded bg-light">
                            <i class="bi bi-target display-4 text-primary mb-3"></i>
                            <h4 id="predictionResult" class="text-primary">-</h4>
                            <p class="text-muted mb-2">Intensitas Penggunaan TikTok</p>
                            <span id="confidenceLevel" class="badge bg-info">0%</span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6 class="text-secondary mb-3">
                            <i class="bi bi-diagram-3 me-2"></i>Decision Path (Jalur Keputusan)
                        </h6>
                        <div id="decisionPath" class="border rounded p-3 bg-light">
                            <!-- Decision path akan ditampilkan di sini -->
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6 class="text-secondary mb-3">
                        <i class="bi bi-clipboard-data me-2"></i>Detail Input Data
                    </h6>
                    <div class="row" id="inputDataSummary">
                        <!-- Input data summary akan ditampilkan di sini -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual Decision Tree Diagram -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Visual Decision Tree Diagram
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="zoomIn()">
                            <i class="bi bi-zoom-in"></i> Zoom In
                        </button>
                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="zoomOut()">
                            <i class="bi bi-zoom-out"></i> Zoom Out
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm me-2" onclick="resetZoom()">
                            <i class="bi bi-arrows-angle-contract"></i> Reset
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" onclick="downloadTreeImage()">
                            <i class="bi bi-download"></i> Download PNG
                        </button>
                    </div>
                </div>
                
                <div id="treeCanvasContainer" class="border rounded p-2" style="background: #f8f9fa; overflow: auto; position: relative;">
                    <canvas id="treeCanvas" width="1200" height="600" style="cursor: move; display: block; margin: 0 auto;"></canvas>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Klik dan drag untuk menggeser diagram. Gunakan zoom untuk melihat detail lebih jelas.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@if($gainAnalysis)
<!-- Gain Comparison Analysis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="bi bi-bar-chart-line me-2"></i>
                    🎯 Gain Comparison Analysis - Setiap Node
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Penjelasan:</strong> Tabel ini menunjukkan perbandingan Information Gain dan Gain Ratio untuk setiap atribut pada setiap node dalam decision tree. Atribut dengan Gain Ratio tertinggi akan dipilih sebagai split point.
                </div>
                
                @php
                    function renderNodeGainTable($node, $depth = 0, &$nodeNumber = 1) {
                        if ($node['type'] === 'leaf') {
                            echo '<div class="mb-3">';
                            echo '<h6 class="text-success"><i class="bi bi-leaf"></i> Node ' . $nodeNumber . ' (Leaf) - Depth ' . $node['depth'] . '</h6>';
                            echo '<div class="alert alert-success" role="alert">';
                            echo '<strong>Hasil Klasifikasi:</strong> ' . ucfirst($node['class']) . ' ';
                            echo '<span class="badge bg-success">' . number_format($node['confidence'] * 100, 1) . '% confidence</span>';
                            echo '<br><small>Samples: ' . $node['samples'] . '</small>';
                            echo '</div>';
                            echo '</div>';
                            $nodeNumber++;
                            return;
                        }
                        
                        echo '<div class="mb-4">';
                        echo '<h5 class="text-primary"><i class="bi bi-node-plus"></i> Node ' . $nodeNumber . ' (Split) - Depth ' . $node['depth'] . '</h5>';
                        echo '<p><strong>Split Attribute:</strong> <span class="badge bg-primary">' . ucfirst(str_replace('_', ' ', $node['split_attribute'])) . '</span> ';
                        echo '<strong>Samples:</strong> ' . $node['samples'] . ' ';
                        echo '<strong>Entropy:</strong> ' . number_format($node['entropy'], 4) . '</p>';
                        
                        // Tampilkan perhitungan entropy untuk node ini
                        if (isset($node['class_distribution'])) {
                            echo '<div class="alert alert-info" role="alert">';
                            echo '<h6 class="alert-heading"><i class="bi bi-calculator me-2"></i>Perhitungan Entropy Node ' . $nodeNumber . '</h6>';
                            echo '<div class="row">';
                            echo '<div class="col-md-8">';
                            
                            $total = $node['samples'];
                            $entropyCalc = 'Entropy(S) = ';
                            $entropyParts = [];
                            
                            if (is_array($node['class_distribution'])) {
                                foreach ($node['class_distribution'] as $class => $count) {
                                    $proportion = $count / $total;
                                    $entropyParts[] = "(-{$count}/{$total} × log₂({$count}/{$total}))";
                                }
                                $entropyCalc .= implode(' + ', $entropyParts);
                            }
                            
                            echo '<p class="mb-2"><strong>Formula:</strong><br>';
                            echo '<code>' . $entropyCalc . '</code></p>';
                            echo '<p class="mb-2"><strong>Distribusi Kelas:</strong><br>';
                            
                            if (is_array($node['class_distribution'])) {
                                foreach ($node['class_distribution'] as $class => $count) {
                                    $proportion = $count / $total;
                                    echo ucfirst($class) . ': ' . $count . ' sampel (' . number_format($proportion * 100, 1) . '%)<br>';
                                }
                            }
                            
                            echo '</p>';
                            echo '</div>';
                            echo '<div class="col-md-4 text-center">';
                            echo '<div class="bg-primary text-white rounded p-3">';
                            echo '<h4>' . number_format($node['entropy'], 4) . '</h4>';
                            echo '<small>Hasil Entropy</small>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        if (isset($node['gain_comparison']) && !empty($node['gain_comparison'])) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-bordered table-hover">';
                            echo '<thead class="table-dark">';
                            echo '<tr>';
                            echo '<th>Atribut</th>';
                            echo '<th>Information Gain</th>';
                            echo '<th>Gain Ratio</th>';
                            echo '<th>Rumus Perhitungan</th>';
                            echo '<th>Status</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            // Sort by gain ratio descending
                            $sortedGains = $node['gain_comparison'];
                            uasort($sortedGains, function($a, $b) {
                                return $b['gain_ratio'] <=> $a['gain_ratio'];
                            });
                            
                            foreach ($sortedGains as $attribute => $stats) {
                                $selected = $stats['selected'];
                                $rowClass = $selected ? 'table-success' : '';
                                $statusIcon = $selected ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-secondary"></i>';
                                $statusText = $selected ? 'DIPILIH' : '';
                                
                                echo '<tr class="' . $rowClass . '">';
                                echo '<td><strong>' . ucfirst(str_replace('_', ' ', $attribute)) . '</strong></td>';
                                echo '<td>' . number_format($stats['information_gain'], 6) . '</td>';
                                echo '<td><strong>' . number_format($stats['gain_ratio'], 6) . '</strong></td>';
                                
                                // Rumus perhitungan untuk atribut ini
                                echo '<td>';
                                echo '<button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#formula-' . $nodeNumber . '-' . str_replace('_', '-', $attribute) . '" aria-expanded="false">';
                                echo '<i class="bi bi-eye me-1"></i>Lihat Rumus';
                                echo '</button>';
                                echo '<div class="collapse mt-2" id="formula-' . $nodeNumber . '-' . str_replace('_', '-', $attribute) . '">';
                                echo '<div class="card card-body bg-light">';
                                echo '<small>';
                                echo '<strong>Information Gain:</strong><br>';
                                echo 'IG(S,' . ucfirst($attribute) . ') = Entropy(S) - Σ(|Sv|/|S|) × Entropy(Sv)<br>';
                                echo 'IG = ' . number_format($node['entropy'], 4) . ' - ';
                                
                                // Tampilkan detailed weighted entropy calculation
                                if (isset($stats['split_details']['groups'])) {
                                    echo '[';
                                    $weightedParts = [];
                                    foreach ($stats['split_details']['groups'] as $value => $details) {
                                        $part = '(' . $details['count'] . '/' . $stats['split_details']['total_samples'] . ') × ' . number_format($details['entropy'], 4);
                                        $weightedParts[] = $part;
                                    }
                                    echo implode(' + ', $weightedParts) . ']<br>';
                                    echo 'IG = ' . number_format($node['entropy'], 4) . ' - ' . number_format($stats['split_details']['total_weighted_entropy'], 4) . '<br>';
                                } else {
                                    echo '[weighted entropy setelah split]<br>';
                                }
                                
                                echo '= <strong>' . number_format($stats['information_gain'], 6) . '</strong><br><br>';
                                
                                // Detail perhitungan weighted entropy
                                if (isset($stats['split_details']['groups'])) {
                                    echo '<div class="border-top pt-2 mt-2">';
                                    echo '<strong>Detail Weighted Entropy:</strong><br>';
                                    foreach ($stats['split_details']['groups'] as $value => $details) {
                                        echo '<small class="text-muted">';
                                        echo '• ' . ucfirst($attribute) . ' = "' . $value . '": ';
                                        echo $details['count'] . ' samples<br>';
                                        
                                        // Tampilkan distribusi kelas
                                        echo '  Distribusi kelas: ';
                                        $classDetails = [];
                                        foreach ($details['class_distribution'] as $class => $count) {
                                            $classDetails[] = ucfirst($class) . ': ' . $count;
                                        }
                                        echo implode(', ', $classDetails) . '<br>';
                                        
                                        // Tampilkan perhitungan entropy detail
                                        if (isset($details['entropy_calculation']) && !empty($details['entropy_calculation'])) {
                                            echo '  Entropy calculation: ';
                                            $entropyParts = [];
                                            foreach ($details['entropy_calculation'] as $calc) {
                                                $entropyParts[] = '-(' . $calc['count'] . '/' . $details['count'] . ') × log₂(' . $calc['count'] . '/' . $details['count'] . ')';
                                            }
                                            echo implode(' + ', $entropyParts) . '<br>';
                                        }
                                        
                                        echo '  Entropy = ' . number_format($details['entropy'], 4);
                                        echo ' → (' . number_format($details['proportion'], 3) . ') × ' . number_format($details['entropy'], 4);
                                        echo ' = ' . number_format($details['weighted_contribution'], 4) . '<br>';
                                        echo '</small>';
                                    }
                                    echo '<strong>Total Weighted Entropy = ' . number_format($stats['split_details']['total_weighted_entropy'], 4) . '</strong>';
                                    echo '</div>';
                                }
                                
                                echo '<br><strong>Split Information:</strong><br>';
                                echo 'SplitInfo(S,' . ucfirst($attribute) . ') = -Σ(|Sv|/|S|) × log₂(|Sv|/|S|)<br>';
                                
                                // Tampilkan perhitungan SplitInfo detail
                                if (isset($stats['split_details']['groups'])) {
                                    echo 'SplitInfo = ';
                                    $splitParts = [];
                                    foreach ($stats['split_details']['groups'] as $value => $details) {
                                        $proportion = $details['count'] / $stats['split_details']['total_samples'];
                                        $splitParts[] = '-(' . $details['count'] . '/' . $stats['split_details']['total_samples'] . ') × log₂(' . $details['count'] . '/' . $stats['split_details']['total_samples'] . ')';
                                    }
                                    echo implode(' + ', $splitParts) . '<br>';
                                    
                                    // Tampilkan nilai numerik
                                    echo 'SplitInfo = ';
                                    $splitNumeric = [];
                                    foreach ($stats['split_details']['groups'] as $value => $details) {
                                        $proportion = $details['count'] / $stats['split_details']['total_samples'];
                                        $logValue = $proportion > 0 ? log($proportion, 2) : 0;
                                        $contribution = -$proportion * $logValue;
                                        $splitNumeric[] = number_format($contribution, 4);
                                    }
                                    echo implode(' + ', $splitNumeric);
                                    
                                    if (isset($stats['split_details']['split_info'])) {
                                        echo ' = <strong>' . number_format($stats['split_details']['split_info'], 6) . '</strong><br>';
                                    }
                                }
                                
                                echo '<br><strong>Gain Ratio:</strong><br>';
                                echo 'GR(S,' . ucfirst($attribute) . ') = IG(S,' . ucfirst($attribute) . ') / SplitInfo(S,' . ucfirst($attribute) . ')<br>';
                                
                                if (isset($stats['split_details']['split_info'])) {
                                    echo 'GR = ' . number_format($stats['information_gain'], 6) . ' / ' . number_format($stats['split_details']['split_info'], 6) . '<br>';
                                } else {
                                    echo 'GR = ' . number_format($stats['information_gain'], 6) . ' / SplitInfo<br>';
                                }
                                
                                echo '= <strong>' . number_format($stats['gain_ratio'], 6) . '</strong>';
                                echo '</small>';
                                echo '</div>';
                                echo '</div>';
                                echo '</td>';
                                
                                echo '<td>' . $statusIcon . ' ' . $statusText . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        }
                        
                        echo '<hr>';
                        echo '</div>';
                        
                        $nodeNumber++;
                        
                        // Recursively render children
                        if (isset($node['children']) && !empty($node['children'])) {
                            foreach ($node['children'] as $value => $child) {
                                echo '<div class="ms-4 border-start border-3 border-primary ps-3 mb-3">';
                                echo '<h6 class="text-secondary">Branch: ' . $node['split_attribute'] . ' = "' . $value . '"</h6>';
                                renderNodeGainTable($child, $depth + 1, $nodeNumber);
                                echo '</div>';
                            }
                        }
                    }
                    
                    $nodeCounter = 1;
                    renderNodeGainTable($gainAnalysis, 0, $nodeCounter);
                @endphp
            </div>
        </div>
    </div>
</div>
@endif

<!-- Mathematical Formulas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-calculator me-2"></i>
                    🧮 Rumus Matematika Algoritma C4.5
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Penjelasan:</strong> Berikut adalah rumus-rumus matematika yang digunakan dalam algoritma C4.5 untuk menghitung nilai Information Gain dan Gain Ratio.
                </div>
                
                <div class="row">
                    <!-- Entropy Formula -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bi bi-1-circle me-2"></i>Entropy</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Mengukur ketidakpastian atau kekacauan dalam dataset:</p>
                                <div class="bg-light p-3 rounded border">
                                    <div class="text-center">
                                        <strong style="font-size: 1.1em;">Entropy(S) = -∑ p<sub>i</sub> × log<sub>2</sub>(p<sub>i</sub>)</strong>
                                    </div>
                                </div>
                                <div class="mt-2 small">
                                    <strong>Dimana:</strong><br>
                                    • <strong>S</strong> = Dataset/subset<br>
                                    • <strong>p<sub>i</sub></strong> = Proporsi sampel dari kelas i<br>
                                    • <strong>i</strong> = 1 sampai n (jumlah kelas)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Gain Formula -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="bi bi-2-circle me-2"></i>Information Gain</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Pengurangan entropy setelah split berdasarkan atribut:</p>
                                <div class="bg-light p-3 rounded border">
                                    <div class="text-center">
                                        <strong style="font-size: 1.1em;">IG(S,A) = Entropy(S) - ∑ (|S<sub>v</sub>|/|S|) × Entropy(S<sub>v</sub>)</strong>
                                    </div>
                                </div>
                                <div class="mt-2 small">
                                    <strong>Dimana:</strong><br>
                                    • <strong>S</strong> = Dataset asli<br>
                                    • <strong>A</strong> = Atribut yang diuji<br>
                                    • <strong>S<sub>v</sub></strong> = Subset dengan nilai v dari atribut A<br>
                                    • <strong>|S|</strong> = Jumlah sampel dalam S
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Split Information Formula -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-3-circle me-2"></i>Split Information</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Mengukur informasi yang dihasilkan dari pembagian atribut:</p>
                                <div class="bg-light p-3 rounded border">
                                    <div class="text-center">
                                        <strong style="font-size: 1.1em;">SplitInfo(S,A) = -∑ (|S<sub>v</sub>|/|S|) × log<sub>2</sub>(|S<sub>v</sub>|/|S|)</strong>
                                    </div>
                                </div>
                                <div class="mt-2 small">
                                    <strong>Dimana:</strong><br>
                                    • <strong>S</strong> = Dataset asli<br>
                                    • <strong>A</strong> = Atribut yang diuji<br>
                                    • <strong>S<sub>v</sub></strong> = Subset dengan nilai v dari atribut A<br>
                                    • <strong>v</strong> = Setiap nilai unik dalam atribut A
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gain Ratio Formula -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-4-circle me-2"></i>Gain Ratio (Final)</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Normalisasi Information Gain untuk mengatasi bias:</p>
                                <div class="bg-light p-3 rounded border">
                                    <div class="text-center">
                                        <strong style="font-size: 1.1em;">GainRatio(S,A) = IG(S,A) / SplitInfo(S,A)</strong>
                                    </div>
                                </div>
                                <div class="mt-2 small">
                                    <strong>Dimana:</strong><br>
                                    • <strong>IG(S,A)</strong> = Information Gain<br>
                                    • <strong>SplitInfo(S,A)</strong> = Split Information<br>
                                    • Atribut dengan <strong>Gain Ratio tertinggi</strong> dipilih untuk split
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Example Calculation -->
                <div class="alert alert-light border-secondary" role="alert">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Contoh Perhitungan</h6>
                            <p class="mb-1">Misalnya untuk dataset dengan 2 kelas (Tinggi: 6 sampel, Rendah: 4 sampel):</p>
                            <p class="mb-1"><strong>Entropy(S)</strong> = -(6/10)×log₂(6/10) - (4/10)×log₂(4/10) = 0.971</p>
                            <p class="mb-0">Kemudian hitung Information Gain dan Gain Ratio untuk setiap atribut kandidat.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-primary text-white rounded p-3">
                                <i class="bi bi-calculator display-6"></i><br>
                                <small>Algoritma C4.5 memilih atribut dengan <strong>Gain Ratio</strong> tertinggi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entropy & Information Analysis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-graph-up-arrow me-2"></i>
                    Entropy & Information Analysis
                </h6>
            </div>
            <div class="card-body">
                <div id="entropyTreeView" class="border rounded p-3" style="max-height: 500px; overflow-y: auto; background-color: #f8f9fa;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-primary">Decision Tree Structure dengan Entropy Data</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="expandAllNodes()">
                            <i class="bi bi-arrows-expand"></i> Expand All
                        </button>
                    </div>
                    <div id="treeStructure">
                        <!-- Tree akan di-render oleh JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tree Statistics -->
@if(isset($modelData['tree_stats']))
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-bar-chart me-2"></i>
                    Tree Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="border-start border-primary border-4 ps-3 mb-3">
                            <h6 class="text-primary">Total Nodes</h6>
                            <h4>{{ $modelData['tree_stats']['total_nodes'] }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-success border-4 ps-3 mb-3">
                            <h6 class="text-success">Leaf Nodes</h6>
                            <h4>{{ $modelData['tree_stats']['leaf_nodes'] }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-info border-4 ps-3 mb-3">
                            <h6 class="text-info">Internal Nodes</h6>
                            <h4>{{ $modelData['tree_stats']['internal_nodes'] }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-warning border-4 ps-3 mb-3">
                            <h6 class="text-warning">Max Depth</h6>
                            <h4>{{ $modelData['tree_stats']['max_depth'] }}</h4>
                        </div>
                    </div>
                </div>
                
                @if(isset($modelData['tree_stats']['attributes_used']))
                <div class="mt-3">
                    <h6 class="text-secondary">Attributes Used:</h6>
                    @foreach(array_unique($modelData['tree_stats']['attributes_used']) as $attr)
                        <span class="badge bg-secondary me-1">{{ $attr }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Model Metrics
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Training Accuracy:</span>
                            <span class="badge bg-success">{{ number_format($modelData['train_metrics']['accuracy'] * 100, 2) }}%</span>
                        </div>
                        <div class="progress mt-1" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $modelData['train_metrics']['accuracy'] * 100 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Testing Accuracy:</span>
                            <span class="badge bg-primary">{{ number_format($modelData['test_metrics']['accuracy'] * 100, 2) }}%</span>
                        </div>
                        <div class="progress mt-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $modelData['test_metrics']['accuracy'] * 100 }}%"></div>
                        </div>
                    </div>
                    
                    @if(isset($modelData['train_metrics']['precision']) && is_array($modelData['train_metrics']['precision']))
                    <div class="col-6">
                        <small class="text-muted">Avg Precision:</small><br>
                        <strong>{{ number_format(array_sum($modelData['train_metrics']['precision']) / count($modelData['train_metrics']['precision']) * 100, 1) }}%</strong>
                    </div>
                    @endif
                    
                    @if(isset($modelData['train_metrics']['recall']) && is_array($modelData['train_metrics']['recall']))
                    <div class="col-6">
                        <small class="text-muted">Avg Recall:</small><br>
                        <strong>{{ number_format(array_sum($modelData['train_metrics']['recall']) / count($modelData['train_metrics']['recall']) * 100, 1) }}%</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Per-Class Metrics -->
@if(isset($modelData['train_metrics']['precision']) && is_array($modelData['train_metrics']['precision']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-pie-chart me-2"></i>
                    Per-Class Performance Metrics
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($modelData['train_metrics']['precision'] as $class => $precision)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-center text-primary">{{ ucfirst($class) }}</h6>
                            <hr>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">Precision</small><br>
                                    <strong class="text-success">{{ number_format($precision * 100, 1) }}%</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Recall</small><br>
                                    <strong class="text-info">{{ number_format($modelData['train_metrics']['recall'][$class] * 100, 1) }}%</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">F1-Score</small><br>
                                    <strong class="text-warning">{{ number_format($modelData['train_metrics']['f1_score'][$class] * 100, 1) }}%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Decision Rules -->
@if(isset($modelData['rules']))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="bi bi-list-check me-2"></i>
                    Decision Rules ({{ count($modelData['rules']) }} rules)
                </h6>
            </div>
            <div class="card-body">
                <div style="max-height: 400px; overflow-y: auto;">
                    @foreach($modelData['rules'] as $index => $rule)
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="text-primary mb-2">
                                    <i class="bi bi-arrow-right-circle me-2"></i>
                                    Rule {{ $index + 1 }}
                                </h6>
                                
                                @if(isset($rule['conditions']) && is_array($rule['conditions']))
                                <div class="mb-2">
                                    <small class="text-muted">Conditions:</small><br>
                                    @foreach($rule['conditions'] as $condIndex => $condition)
                                        <span class="badge bg-secondary me-1">{{ $condition }}</span>
                                        @if($condIndex < count($rule['conditions']) - 1)
                                            <span class="text-muted"> AND </span>
                                        @endif
                                    @endforeach
                                </div>
                                @endif
                                
                                <div class="mb-2">
                                    <small class="text-muted">Prediction:</small>
                                    <span class="badge bg-success ms-1">{{ $rule['class'] ?? 'Unknown' }}</span>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                @if(isset($rule['confidence']))
                                <div class="mb-1">
                                    <small class="text-muted">Confidence</small><br>
                                    <span class="badge bg-info">{{ number_format($rule['confidence'] * 100, 1) }}%</span>
                                </div>
                                @endif
                                
                                @if(isset($rule['samples']))
                                <div>
                                    <small class="text-muted">Samples</small><br>
                                    <span class="badge bg-warning text-dark">{{ $rule['samples'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endif

<!-- Session Details -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="bi bi-info-circle me-2"></i>
                    Session Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Session ID:</strong></td>
                                <td>{{ $session->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Algorithm:</strong></td>
                                <td><span class="badge bg-primary">{{ $session->algorithm }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $session->created_at->format('d F Y, H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Training Time:</strong></td>
                                <td>{{ number_format($session->training_time, 3) }} seconds</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Train Data:</strong></td>
                                <td><span class="badge bg-info">{{ $session->train_data_count }} samples</span></td>
                            </tr>
                            <tr>
                                <td><strong>Test Data:</strong></td>
                                <td><span class="badge bg-secondary">{{ $session->test_data_count }} samples</span></td>
                            </tr>
                            <tr>
                                <td><strong>Final Accuracy:</strong></td>
                                <td><span class="badge {{ $session->accuracy >= 0.8 ? 'bg-success' : ($session->accuracy >= 0.6 ? 'bg-warning' : 'bg-danger') }}">{{ number_format($session->accuracy * 100, 2) }}%</span></td>
                            </tr>
                            @if($session->parameters)
                            <tr>
                                <td><strong>Parameters:</strong></td>
                                <td>
                                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#parametersCollapse">
                                        <i class="bi bi-gear"></i> View
                                    </button>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                @if($session->parameters)
                <div class="collapse mt-3" id="parametersCollapse">
                    <div class="border rounded p-3 bg-light">
                        <h6 class="text-secondary">Training Parameters:</h6>
                        <pre class="mb-0"><code>{{ json_encode(json_decode($session->parameters), JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Tree data dari backend
const treeData = @json($modelData['tree'] ?? []);

console.log('Tree Data Structure:', treeData);
console.log('Tree Data Type:', typeof treeData);
console.log('Tree Keys:', Object.keys(treeData || {}));

// Visual Tree Canvas Variables
let canvas, ctx;
let scale = 1;
let offsetX = 0, offsetY = 0;
let isDragging = false;
let lastMouseX = 0, lastMouseY = 0;

// Tree visualization class
class TreeVisualizer {
    constructor(canvasId, treeData) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.treeData = treeData;
        this.nodes = [];
        this.scale = 1;
        this.offsetX = 0;
        this.offsetY = 50;
        
        // Node styling
        this.nodeWidth = 180;
        this.nodeHeight = 80;
        this.levelHeight = 120;
        this.horizontalSpacing = 220;
        
        this.setupCanvas();
        this.calculateLayout();
        this.setupEventListeners();
    }
    
    setupCanvas() {
        // High DPI support
        const dpr = window.devicePixelRatio || 1;
        const rect = this.canvas.getBoundingClientRect();
        
        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;
        this.ctx.scale(dpr, dpr);
        
        this.canvas.style.width = rect.width + 'px';
        this.canvas.style.height = rect.height + 'px';
    }
    
    calculateLayout() {
        this.nodes = [];
        if (!this.treeData || typeof this.treeData !== 'object') return;
        
        this.assignPositions(this.treeData, 600, 50, 0, null);
    }
    
    assignPositions(node, x, y, depth, parentX) {
        const nodeInfo = {
            x: x,
            y: y,
            depth: depth,
            data: node,
            parentX: parentX,
            parentY: parentX ? y - this.levelHeight : null
        };
        
        this.nodes.push(nodeInfo);
        
        if (node.children && typeof node.children === 'object') {
            const children = Object.entries(node.children);
            const childrenCount = children.length;
            
            if (childrenCount > 0) {
                const totalWidth = (childrenCount - 1) * this.horizontalSpacing;
                const startX = x - totalWidth / 2;
                
                children.forEach(([value, child], index) => {
                    const childX = startX + (index * this.horizontalSpacing);
                    const childY = y + this.levelHeight;
                    
                    // Store the branch value
                    child._branchValue = value;
                    child._branchAttribute = node.attribute;
                    
                    this.assignPositions(child, childX, childY, depth + 1, x);
                });
            }
        }
    }
    
    draw() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.ctx.save();
        this.ctx.scale(this.scale, this.scale);
        this.ctx.translate(this.offsetX, this.offsetY);
        
        // Draw connections first
        this.drawConnections();
        
        // Draw nodes
        this.nodes.forEach(node => this.drawNode(node));
        
        this.ctx.restore();
    }
    
    drawConnections() {
        this.ctx.strokeStyle = '#6c757d';
        this.ctx.lineWidth = 2;
        
        this.nodes.forEach(node => {
            if (node.parentX !== null) {
                this.ctx.beginPath();
                this.ctx.moveTo(node.parentX, node.parentY + this.nodeHeight/2);
                this.ctx.lineTo(node.x, node.y - 10);
                this.ctx.stroke();
                
                // Draw branch label
                const midX = (node.parentX + node.x) / 2;
                const midY = (node.parentY + node.y) / 2;
                
                if (node.data._branchValue) {
                    this.ctx.fillStyle = '#fff';
                    this.ctx.fillRect(midX - 30, midY - 10, 60, 20);
                    this.ctx.strokeRect(midX - 30, midY - 10, 60, 20);
                    
                    this.ctx.fillStyle = '#495057';
                    this.ctx.font = 'bold 11px Arial';
                    this.ctx.textAlign = 'center';
                    this.ctx.fillText(node.data._branchValue, midX, midY + 4);
                }
            }
        });
    }
    
    drawNode(nodeInfo) {
        const x = nodeInfo.x - this.nodeWidth / 2;
        const y = nodeInfo.y;
        const node = nodeInfo.data;
        
        // Check if this node is highlighted (part of prediction path)
        const isHighlighted = highlightedNodes.some(hNode => 
            hNode.x === nodeInfo.x && hNode.y === nodeInfo.y
        );
        
        // Determine node color based on type
        let bgColor, borderColor, textColor;
        
        if (node.type === 'leaf') {
            if (isHighlighted) {
                bgColor = '#c3e6cb'; // Brighter green for highlighted leaf
                borderColor = '#28a745';
                textColor = '#155724';
            } else {
                bgColor = '#d4edda';
                borderColor = '#28a745';
                textColor = '#155724';
            }
        } else {
            const entropyLevel = node.entropy || 0;
            if (isHighlighted) {
                // Brighter colors for highlighted nodes
                if (entropyLevel > 1) {
                    bgColor = '#f1b0b7';
                    borderColor = '#dc3545';
                    textColor = '#721c24';
                } else if (entropyLevel > 0.5) {
                    bgColor = '#ffeaa7';
                    borderColor = '#ffc107';
                    textColor = '#856404';
                } else {
                    bgColor = '#a8dadc';
                    borderColor = '#17a2b8';
                    textColor = '#0c5460';
                }
            } else {
                // Normal colors
                if (entropyLevel > 1) {
                    bgColor = '#f8d7da';
                    borderColor = '#dc3545';
                    textColor = '#721c24';
                } else if (entropyLevel > 0.5) {
                    bgColor = '#fff3cd';
                    borderColor = '#ffc107';
                    textColor = '#856404';
                } else {
                    bgColor = '#d1ecf1';
                    borderColor = '#17a2b8';
                    textColor = '#0c5460';
                }
            }
        }
        
        // Draw node background
        this.ctx.fillStyle = bgColor;
        this.ctx.fillRect(x, y, this.nodeWidth, this.nodeHeight);
        
        // Draw border (thicker for highlighted nodes)
        this.ctx.strokeStyle = borderColor;
        this.ctx.lineWidth = isHighlighted ? 4 : 2;
        this.ctx.strokeRect(x, y, this.nodeWidth, this.nodeHeight);
        
        // Draw highlight indicator for path nodes
        if (isHighlighted) {
            this.ctx.fillStyle = borderColor;
            this.ctx.fillRect(x - 5, y - 5, 10, 10); // Top-left indicator
            this.ctx.fillRect(x + this.nodeWidth - 5, y - 5, 10, 10); // Top-right indicator
        }
        
        // Draw node content
        this.ctx.fillStyle = textColor;
        this.ctx.textAlign = 'center';
        
        if (node.type === 'leaf') {
            // Leaf node content
            this.ctx.font = 'bold 12px Arial';
            this.ctx.fillText('LEAF', x + this.nodeWidth/2, y + 20);
            
            this.ctx.font = 'bold 14px Arial';
            this.ctx.fillText((node.class || 'Unknown').toUpperCase(), x + this.nodeWidth/2, y + 38);
            
            this.ctx.font = '10px Arial';
            this.ctx.fillText(`${node.samples || 0} samples`, x + this.nodeWidth/2, y + 52);
            
            if (node.confidence) {
                this.ctx.fillText(`${(node.confidence * 100).toFixed(1)}%`, x + this.nodeWidth/2, y + 65);
            }
        } else {
            // Internal node content
            this.ctx.font = 'bold 12px Arial';
            this.ctx.fillText('SPLIT', x + this.nodeWidth/2, y + 15);
            
            this.ctx.font = 'bold 13px Arial';
            this.ctx.fillText(node.attribute || 'Unknown', x + this.nodeWidth/2, y + 32);
            
            this.ctx.font = '9px Arial';
            if (node.entropy) {
                this.ctx.fillText(`Entropy: ${node.entropy.toFixed(3)}`, x + this.nodeWidth/2, y + 48);
            }
            if (node.gain_ratio) {
                this.ctx.fillText(`Gain: ${node.gain_ratio.toFixed(3)}`, x + this.nodeWidth/2, y + 60);
            }
            this.ctx.fillText(`${node.samples || 0} samples`, x + this.nodeWidth/2, y + 72);
        }
    }
    
    setupEventListeners() {
        let isDragging = false;
        let lastX, lastY;
        
        this.canvas.addEventListener('mousedown', (e) => {
            isDragging = true;
            lastX = e.clientX;
            lastY = e.clientY;
            this.canvas.style.cursor = 'grabbing';
        });
        
        this.canvas.addEventListener('mousemove', (e) => {
            if (isDragging) {
                const deltaX = e.clientX - lastX;
                const deltaY = e.clientY - lastY;
                
                this.offsetX += deltaX / this.scale;
                this.offsetY += deltaY / this.scale;
                
                lastX = e.clientX;
                lastY = e.clientY;
                
                this.draw();
            }
        });
        
        this.canvas.addEventListener('mouseup', () => {
            isDragging = false;
            this.canvas.style.cursor = 'move';
        });
        
        this.canvas.addEventListener('mouseleave', () => {
            isDragging = false;
            this.canvas.style.cursor = 'move';
        });
        
        // Zoom with mouse wheel
        this.canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            
            const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
            const newScale = Math.max(0.3, Math.min(3, this.scale * zoomFactor));
            
            if (newScale !== this.scale) {
                this.scale = newScale;
                this.draw();
            }
        });
    }
    
    zoom(factor) {
        this.scale = Math.max(0.3, Math.min(3, this.scale * factor));
        this.draw();
    }
    
    resetView() {
        this.scale = 1;
        this.offsetX = 0;
        this.offsetY = 50;
        this.draw();
    }
}

// Global tree visualizer instance
let treeVisualizer;

// Prediction variables
let currentPredictionPath = [];
let highlightedNodes = [];

// Canvas control functions
function zoomIn() {
    if (treeVisualizer) {
        treeVisualizer.zoom(1.2);
    }
}

function zoomOut() {
    if (treeVisualizer) {
        treeVisualizer.zoom(0.8);
    }
}

function resetZoom() {
    if (treeVisualizer) {
        treeVisualizer.resetView();
    }
}

function downloadTreeImage() {
    if (!treeVisualizer) return;
    
    // Create temporary canvas for export
    const exportCanvas = document.createElement('canvas');
    exportCanvas.width = 1200;
    exportCanvas.height = 800;
    const exportCtx = exportCanvas.getContext('2d');
    
    // Fill white background
    exportCtx.fillStyle = '#ffffff';
    exportCtx.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
    
    // Draw the tree
    exportCtx.save();
    exportCtx.translate(100, 100);
    
    // Temporarily use export canvas
    const originalCanvas = treeVisualizer.canvas;
    const originalCtx = treeVisualizer.ctx;
    
    treeVisualizer.canvas = exportCanvas;
    treeVisualizer.ctx = exportCtx;
    treeVisualizer.scale = 0.8;
    treeVisualizer.offsetX = 0;
    treeVisualizer.offsetY = 0;
    
    treeVisualizer.draw();
    
    // Restore original canvas
    treeVisualizer.canvas = originalCanvas;
    treeVisualizer.ctx = originalCtx;
    
    exportCtx.restore();
    
    // Download
    const link = document.createElement('a');
    link.download = `decision_tree_session_{{ $session->id }}.png`;
    link.href = exportCanvas.toDataURL();
    link.click();
}

// Single prediction functions
function clearPredictionForm() {
    document.getElementById('singlePredictionForm').reset();
    document.getElementById('predictionResultContainer').style.display = 'none';
    
    // Clear highlights from tree
    if (treeVisualizer) {
        highlightedNodes = [];
        treeVisualizer.draw();
    }
}

function predictSingleData(inputData) {
    if (!treeData || typeof treeData !== 'object') {
        return { prediction: 'error', path: [], confidence: 0 };
    }
    
    const path = [];
    let currentNode = treeData;
    
    while (currentNode && currentNode.type !== 'leaf') {
        const attributeValue = inputData[currentNode.attribute];
        
        path.push({
            attribute: currentNode.attribute,
            value: attributeValue,
            entropy: currentNode.entropy,
            samples: currentNode.samples
        });
        
        if (currentNode.children && currentNode.children[attributeValue]) {
            currentNode = currentNode.children[attributeValue];
        } else {
            // Value not found, return most frequent class
            break;
        }
    }
    
    const result = {
        prediction: currentNode ? (currentNode.class || 'unknown') : 'unknown',
        confidence: currentNode ? (currentNode.confidence || 1.0) : 0.5,
        path: path,
        leafNode: currentNode
    };
    
    return result;
}

function displayPredictionResult(inputData, result) {
    // Show result container
    document.getElementById('predictionResultContainer').style.display = 'block';
    
    // Display prediction result
    const predictionElement = document.getElementById('predictionResult');
    const confidenceElement = document.getElementById('confidenceLevel');
    
    predictionElement.textContent = result.prediction.toUpperCase();
    predictionElement.className = `text-${getClassColor(result.prediction)}`;
    
    confidenceElement.textContent = `${(result.confidence * 100).toFixed(1)}% Confidence`;
    confidenceElement.className = `badge bg-${getClassColor(result.prediction)}`;
    
    // Display decision path
    displayDecisionPath(result.path, result.leafNode);
    
    // Display input data summary
    displayInputDataSummary(inputData);
    
    // Highlight path in tree visualization
    if (treeVisualizer) {
        highlightPredictionPath(result.path);
    }
    
    // Scroll to result
    document.getElementById('predictionResultContainer').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

function getClassColor(prediction) {
    switch(prediction.toLowerCase()) {
        case 'tinggi': return 'success';
        case 'sedang': return 'warning';
        case 'rendah': return 'danger';
        default: return 'secondary';
    }
}

function displayDecisionPath(path, leafNode) {
    const pathContainer = document.getElementById('decisionPath');
    let pathHtml = '';
    
    if (path.length === 0) {
        pathHtml = '<div class="text-muted">Path langsung ke leaf node</div>';
    } else {
        pathHtml = '<div class="decision-steps">';
        
        path.forEach((step, index) => {
            const stepColor = step.entropy > 1 ? 'danger' : step.entropy > 0.5 ? 'warning' : 'info';
            
            pathHtml += `
                <div class="step mb-3 p-3 border-start border-${stepColor} border-4 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-${stepColor} mb-1">
                                <span class="badge bg-${stepColor} me-2">${index + 1}</span>
                                Evaluasi: ${step.attribute}
                            </h6>
                            <p class="mb-1">
                                Nilai input: <strong>${step.value}</strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Entropy: ${step.entropy ? step.entropy.toFixed(3) : 'N/A'}</small><br>
                            <small class="text-muted">Samples: ${step.samples || 0}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Add final result
        pathHtml += `
            <div class="step mb-0 p-3 border-start border-success border-4 bg-success bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-success mb-1">
                            <span class="badge bg-success me-2"><i class="bi bi-check"></i></span>
                            Hasil Akhir
                        </h6>
                        <p class="mb-1">
                            Klasifikasi: <strong class="text-${getClassColor(leafNode.class)}">${(leafNode.class || 'Unknown').toUpperCase()}</strong>
                        </p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Confidence: ${((leafNode.confidence || 1.0) * 100).toFixed(1)}%</small><br>
                        <small class="text-muted">Samples: ${leafNode.samples || 0}</small>
                    </div>
                </div>
            </div>
        `;
        
        pathHtml += '</div>';
    }
    
    pathContainer.innerHTML = pathHtml;
}

function displayInputDataSummary(inputData) {
    const summaryContainer = document.getElementById('inputDataSummary');
    
    const summaryHtml = `
        <div class="col-md-3 mb-2">
            <div class="text-center p-2 border rounded bg-primary bg-opacity-10">
                <small class="text-muted d-block">Durasi Penggunaan</small>
                <strong class="text-primary">${inputData.durasi_penggunaan}</strong>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="text-center p-2 border rounded bg-info bg-opacity-10">
                <small class="text-muted d-block">Frekuensi Akses</small>
                <strong class="text-info">${inputData.frekuensi_akses}</strong>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="text-center p-2 border rounded bg-warning bg-opacity-10">
                <small class="text-muted d-block">Perhatian Konten</small>
                <strong class="text-warning">${inputData.perhatian_konten}</strong>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="text-center p-2 border rounded bg-secondary bg-opacity-10">
                <small class="text-muted d-block">Penghayatan</small>
                <strong class="text-secondary">${inputData.penghayatan}</strong>
            </div>
        </div>
    `;
    
    summaryContainer.innerHTML = summaryHtml;
}

function highlightPredictionPath(path) {
    if (!treeVisualizer) return;
    
    // Store current path for highlighting
    currentPredictionPath = path;
    
    // Find nodes that match the path
    highlightedNodes = [];
    
    treeVisualizer.nodes.forEach(nodeInfo => {
        const node = nodeInfo.data;
        
        // Check if this node is in the path
        const isInPath = path.some(step => {
            return node.attribute === step.attribute && 
                   Math.abs((node.entropy || 0) - (step.entropy || 0)) < 0.001;
        });
        
        if (isInPath || node.type === 'leaf') {
            highlightedNodes.push(nodeInfo);
        }
    });
    
    // Redraw with highlights
    treeVisualizer.draw();
}

function renderTreeNode(node, depth = 0) {
    const indent = '  '.repeat(depth);
    let html = '';
    
    if (node.type === 'leaf') {
        html += `<div class="tree-leaf" style="margin-left: ${depth * 20}px;">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-leaf text-success me-2"></i>
                <strong class="text-success">LEAF</strong>
                <span class="badge bg-success ms-2">${node.class || 'Unknown'}</span>
                <small class="text-muted ms-2">(${node.samples} samples)</small>
                ${node.confidence ? `<span class="badge bg-info ms-2">${(node.confidence * 100).toFixed(1)}%</span>` : ''}
            </div>
        </div>`;
    } else {
        const entropyColor = node.entropy > 1 ? 'danger' : node.entropy > 0.5 ? 'warning' : 'success';
        
        html += `<div class="tree-node mb-3" style="margin-left: ${depth * 20}px;">
            <div class="card border-${entropyColor} shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-${entropyColor} mb-1">
                                <i class="bi bi-diagram-3 me-2"></i>
                                Split: <strong>${node.attribute || 'Unknown'}</strong>
                            </h6>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <small class="text-muted">Entropy</small><br>
                                    <span class="badge bg-${entropyColor} fs-6">${node.entropy ? node.entropy.toFixed(4) : 'N/A'}</span>
                                </div>
                                <div>
                                    <small class="text-muted">Gain Ratio</small><br>
                                    <span class="badge bg-info fs-6">${node.gain_ratio ? node.gain_ratio.toFixed(4) : 'N/A'}</span>
                                </div>
                                <div>
                                    <small class="text-muted">Samples</small><br>
                                    <span class="badge bg-secondary fs-6">${node.samples || 0}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        
        // Render children
        if (node.children && typeof node.children === 'object') {
            for (const [value, child] of Object.entries(node.children)) {
                html += `<div class="tree-branch" style="margin-left: ${(depth + 1) * 20}px;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-arrow-down-right text-primary me-2"></i>
                        <span class="badge bg-primary">${node.attribute || 'attr'} = ${value}</span>
                    </div>
                    ${renderTreeNode(child, depth + 2)}
                </div>`;
            }
        }
    }
    
    return html;
}

function expandAllNodes() {
    // Toggle semua collapse elements
    const collapseElements = document.querySelectorAll('.collapse');
    collapseElements.forEach(el => {
        new bootstrap.Collapse(el, {show: true});
    });
}

// Render tree saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Loaded, processing tree data...');
    
    if (treeData && typeof treeData === 'object' && Object.keys(treeData).length > 0) {
        try {
            // Initialize visual tree diagram
            const canvas = document.getElementById('treeCanvas');
            if (canvas) {
                treeVisualizer = new TreeVisualizer('treeCanvas', treeData);
                treeVisualizer.draw();
                console.log('Visual tree diagram initialized successfully');
            }
            
            // Render text-based tree
            const treeHtml = renderTreeNode(treeData);
            document.getElementById('treeStructure').innerHTML = treeHtml;
            console.log('Tree rendered successfully');
        } catch (error) {
            console.error('Error rendering tree:', error);
            document.getElementById('treeStructure').innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error rendering tree: ${error.message}
                </div>`;
        }
    } else {
        console.log('No valid tree data found');
        document.getElementById('treeStructure').innerHTML = `
            <div class="text-center text-muted p-4">
                <i class="bi bi-tree fs-2"></i><br>
                <p class="mt-2">No tree data available</p>
                <small>Tree data: ${JSON.stringify(treeData)}</small>
            </div>`;
    }
    
    // Setup single prediction form
    const predictionForm = document.getElementById('singlePredictionForm');
    if (predictionForm) {
        predictionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const inputData = {
                durasi_penggunaan: formData.get('durasi_penggunaan'),
                frekuensi_akses: formData.get('frekuensi_akses'),
                perhatian_konten: parseInt(formData.get('perhatian_konten')),
                penghayatan: parseInt(formData.get('penghayatan'))
            };
            
            console.log('Running prediction with:', inputData);
            
            // Validate input
            if (!inputData.durasi_penggunaan || !inputData.frekuensi_akses || 
                !inputData.perhatian_konten || !inputData.penghayatan) {
                alert('Mohon lengkapi semua field input');
                return;
            }
            
            // Run prediction
            const result = predictSingleData(inputData);
            console.log('Prediction result:', result);
            
            // Display result
            displayPredictionResult(inputData, result);
        });
    }
});
</script>

<style>
.tree-node .card {
    border-left-width: 4px !important;
}

.tree-leaf {
    border-left: 4px solid #28a745;
    padding-left: 12px;
    background-color: #f8fff9;
    border-radius: 4px;
}

.tree-branch {
    position: relative;
}

.tree-branch::before {
    content: '';
    position: absolute;
    left: -10px;
    top: 0;
    bottom: 0;
    border-left: 2px dashed #dee2e6;
}

/* Visual Tree Canvas Styles */
#treeCanvasContainer {
    min-height: 600px;
    max-height: 800px;
    position: relative;
}

#treeCanvas {
    background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%),
                linear-gradient(-45deg, #f8f9fa 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #f8f9fa 75%),
                linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
}

.tree-controls {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Responsive canvas */
@media (max-width: 768px) {
    #treeCanvas {
        width: 100% !important;
        height: 400px !important;
    }
    
    .tree-controls .btn {
        font-size: 12px;
        padding: 4px 8px;
    }
}

/* Loading animation for canvas */
.canvas-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.canvas-loading .spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>

@endsection