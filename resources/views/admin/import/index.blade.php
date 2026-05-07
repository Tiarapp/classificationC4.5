@extends('admin.layout')

@section('title', 'Import Data Kuesioner')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">📂 Import Data Kuesioner</h4>
                    <div>
                        <button onclick="downloadTemplate()" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download"></i> Download Template
                        </button>
                        <a href="{{ route('admin.import.upload') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload"></i> Upload File
                        </a>
                        <a href="{{ route('admin.import.simple') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-upload"></i> Simple Upload
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Import Guide -->
                        <div class="col-md-8">
                            <h5>📋 Panduan Import Data</h5>
                            <div class="alert alert-info">
                                <h6>Format File yang Didukung:</h6>
                                <ul class="mb-2">
                                    <li><strong>Excel:</strong> .xlsx, .xls</li>
                                    <li><strong>CSV:</strong> .csv</li>
                                    <li><strong>Ukuran:</strong> Maksimal 10MB</li>
                                </ul>
                                
                                <h6>Kolom Yang Diperlukan:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kolom</th>
                                                <th>Wajib</th>
                                                <th>Format</th>
                                                <th>Contoh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>nama</code></td>
                                                <td><span class="badge bg-warning">Opsional</span></td>
                                                <td>Text</td>
                                                <td>John Doe</td>
                                            </tr>
                                            <tr>
                                                <td><code>nim</code></td>
                                                <td><span class="badge bg-warning">Opsional</span></td>
                                                <td>Text/Angka</td>
                                                <td>2201001</td>
                                            </tr>
                                            <tr>
                                                <td><code>jurusan</code></td>
                                                <td><span class="badge bg-warning">Opsional</span></td>
                                                <td>Text</td>
                                                <td>Teknik Informatika</td>
                                            </tr>
                                            <tr>
                                                <td><code>semester</code></td>
                                                <td><span class="badge bg-warning">Opsional</span></td>
                                                <td>Angka 1-12</td>
                                                <td>5</td>
                                            </tr>
                                            <tr>
                                                <td><code>durasi_penggunaan</code></td>
                                                <td><span class="badge bg-danger">Wajib</span></td>
                                                <td><=1 jam | 1-3 jam | 3-5 jam | >5 jam</td>
                                                <td>1-3 jam</td>
                                            </tr>
                                            <tr>
                                                <td><code>frekuensi_akses</code></td>
                                                <td><span class="badge bg-danger">Wajib</span></td>
                                                <td>1-2x | 3-5x | >5x</td>
                                                <td>3-5x</td>
                                            </tr>
                                            <tr>
                                                <td><code>perhatian_konten</code></td>
                                                <td><span class="badge bg-warning">Opsional</span></td>
                                                <td>Angka 1-5</td>
                                                <td>3</td>
                                            </tr>
                                            <tr>
                                                <td><code>penghayatan</code></td>
                                                <td><span class="badge bg-warning">Opsional</span></td>
                                                <td>Angka 1-5</td>
                                                <td>4</td>
                                            </tr>
                                            <tr>
                                                <td><code>label_intensitas</code></td>
                                                <td><span class="badge bg-danger">Wajib</span></td>
                                                <td>rendah | sedang | tinggi</td>
                                                <td>sedang</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <h6>💡 Tips Import:</h6>
                                <ul class="mb-0">
                                    <li>Sistem akan otomatis menormalkan format data (contoh: "1-3 jam" = "1 - 3 jam")</li>
                                    <li>Jika NIM kosong, sistem akan generate otomatis</li>
                                    <li>Jika perhatian/penghayatan kosong, akan diisi random 1-5</li>
                                    <li>Download template untuk format yang tepat</li>
                                    <li>Centang "Replace Existing Data" untuk mengganti semua data lama</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Current Statistics -->
                        <div class="col-md-4">
                            <h5>📊 Data Saat Ini</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div id="current-stats">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Memuat statistik...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <h6>📝 Riwayat Import Terbaru</h6>
                                <div id="import-history" class="small">
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                                        <p class="mt-1">Memuat riwayat...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <button onclick="downloadTemplate()" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-download"></i> Download Template Excel
                                </button>
                                <a href="{{ route('admin.import.upload') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-upload"></i> Mulai Import Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentStats();
    
    // Refresh stats every 30 seconds
    setInterval(loadCurrentStats, 30000);
});

async function downloadTemplate() {
    try {
        const response = await fetch('{{ route("admin.import.download-template") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            }
        });

        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'template_import_kuesioner.xlsx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            alert('Error downloading template: ' + response.statusText);
        }
    } catch (error) {
        console.error('Download error:', error);
        alert('Error downloading template');
    }
}

async function loadCurrentStats() {
    try {
        const response = await fetch('{{ route("admin.import.history") }}');
        const data = await response.json();
        
        let statsHtml = `
            <div class="row text-center">
                <div class="col-6">
                    <h4 class="text-primary">${data.total_respondents}</h4>
                    <small>Total Responden</small>
                </div>
                <div class="col-6">
                    <h4 class="text-success">${data.total_datasets}</h4>
                    <small>Total Dataset</small>
                </div>
            </div>
            <hr>
            <h6>Distribusi Label:</h6>
        `;
        
        if (data.distribution) {
            const colors = {rendah: 'success', sedang: 'warning', tinggi: 'danger'};
            for (const [label, count] of Object.entries(data.distribution)) {
                const percentage = data.total_datasets > 0 ? ((count / data.total_datasets) * 100).toFixed(1) : 0;
                statsHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-${colors[label] || 'secondary'}">${label}</span>
                        <span><strong>${count}</strong> (${percentage}%)</span>
                    </div>
                `;
            }
        }
        
        document.getElementById('current-stats').innerHTML = statsHtml;
        
        // Load recent imports
        let historyHtml = '';
        if (data.recent_imports && data.recent_imports.length > 0) {
            const colors = {rendah: 'success', sedang: 'warning', tinggi: 'danger'};
            data.recent_imports.slice(0, 5).forEach(item => {
                const date = new Date(item.created_at).toLocaleDateString('id-ID');
                historyHtml += `
                    <div class="border-bottom pb-1 mb-1">
                        <div class="d-flex justify-content-between">
                            <span>${item.respondent.nama.substring(0, 15)}...</span>
                            <span class="badge bg-${colors[item.label_intensitas]}">${item.label_intensitas}</span>
                        </div>
                        <small class="text-muted">${date}</small>
                    </div>
                `;
            });
        } else {
            historyHtml = '<p class="text-muted">Belum ada data import</p>';
        }
        
        document.getElementById('import-history').innerHTML = historyHtml;
        
    } catch (error) {
        console.error('Error loading stats:', error);
        document.getElementById('current-stats').innerHTML = 
            '<div class="alert alert-danger">Error memuat statistik</div>';
    }
}
</script>
@endsection