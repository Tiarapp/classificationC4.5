@extends('admin.layout')

@section('title', 'Upload File Import')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">📤 Upload File Kuesioner</h4>
                    <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="import-form" action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- File Upload Area -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih File Excel/CSV</label>
                            <div class="border border-dashed border-2 p-4 text-center" 
                                 id="drop-zone" 
                                 style="border-color: #dee2e6; transition: all 0.3s;">
                                <input type="file" 
                                       class="form-control d-none" 
                                       id="file-input" 
                                       name="file" 
                                       accept=".xlsx,.xls,.csv"
                                       required>
                                
                                <div id="drop-zone-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Drag & Drop file atau <button type="button" class="btn btn-link p-0" onclick="document.getElementById('file-input').click()">Browse</button></h5>
                                    <p class="text-muted">Support: .xlsx, .xls, .csv (Max: 10MB)</p>
                                </div>
                                
                                <div id="file-preview" class="d-none">
                                    <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                                    <p class="mb-0"><span id="file-name"></span></p>
                                    <small class="text-muted">Size: <span id="file-size"></span></small>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- File Validation Results -->
                        <div id="validation-results" class="mb-3 d-none"></div>

                        <!-- Import Options -->
                        <div class="mb-4">
                            <h6>⚙️ Opsi Import</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="replace_existing" id="replace_existing" value="1">
                                <label class="form-check-label" for="replace_existing">
                                    <strong>Replace Existing Data</strong>
                                    <small class="text-danger d-block">⚠️ Akan menghapus semua data responden dan dataset yang ada</small>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-lg" disabled>
                                <span id="submit-text">
                                    <i class="fas fa-upload"></i> Mulai Import
                                </span>
                                <span id="submit-loading" class="d-none">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Progress Bar -->
                    <div id="progress-container" class="mt-4 d-none">
                        <h6>📊 Progress Import</h6>
                        <div class="progress mb-2">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p id="progress-text" class="text-center mb-0">Preparing import...</p>
                    </div>
                </div>
            </div>

            <!-- Quick Guide Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">📋 Quick Reference</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Durasi Penggunaan:</h6>
                            <ul class="small">
                                <li><=1 jam, kurang dari 1 jam, 1</li>
                                <li>1-3 jam, antara 1-3 jam, 2</li>
                                <li>3-5 jam, antara 3-5 jam, 3</li>
                                <li>>5 jam, lebih dari 5 jam, 4</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Label Intensitas:</h6>
                            <ul class="small">
                                <li>rendah, low, 1</li>
                                <li>sedang, medium, menengah, 2</li>
                                <li>tinggi, high, 3</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#drop-zone.dragover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

#drop-zone.error {
    border-color: #dc3545 !important;
    background-color: #f8d7da;
}

#drop-zone.success {
    border-color: #198754 !important;
    background-color: #d1e7dd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const form = document.getElementById('import-form');
    
    // Drag and drop functionality
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (validateForm()) {
            // Show loading state before submission
            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');
            
            submitText.classList.add('d-none');
            submitLoading.classList.remove('d-none');
            submitBtn.disabled = true;
            
            // Let form submit normally (no preventDefault)
            // This avoids AJAX timeout issues
        } else {
            e.preventDefault();
        }
    });
});

function handleFileSelect(file) {
    const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                       'application/vnd.ms-excel', 'text/csv'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    // Reset classes
    document.getElementById('drop-zone').className = 'border border-dashed border-2 p-4 text-center';
    
    if (!validTypes.includes(file.type)) {
        showError('File type not supported. Please upload .xlsx, .xls, or .csv file');
        return;
    }
    
    if (file.size > maxSize) {
        showError('File too large. Maximum size is 10MB');
        return;
    }
    
    // Show file preview
    document.getElementById('drop-zone-content').classList.add('d-none');
    document.getElementById('file-preview').classList.remove('d-none');
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('drop-zone').classList.add('success');
    
    // Validate file content
    validateFileContent(file);
}

function clearFile() {
    document.getElementById('file-input').value = '';
    document.getElementById('drop-zone-content').classList.remove('d-none');
    document.getElementById('file-preview').classList.add('d-none');
    document.getElementById('validation-results').classList.add('d-none');
    document.getElementById('submit-btn').disabled = true;
    document.getElementById('drop-zone').className = 'border border-dashed border-2 p-4 text-center';
}

async function validateFileContent(file) {
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch('{{ route("admin.import.validate") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        showValidationResults(result);
        
    } catch (error) {
        console.error('Validation error:', error);
        showError('Error validating file');
    }
}

function showValidationResults(result) {
    const container = document.getElementById('validation-results');
    const submitBtn = document.getElementById('submit-btn');
    
    if (result.valid) {
        container.innerHTML = `
            <div class="alert alert-success">
                <h6><i class="fas fa-check-circle"></i> File Valid!</h6>
                <ul class="mb-0">
                    <li>Rows to import: <strong>${result.row_count}</strong></li>
                    <li>Headers found: ${result.headers.join(', ')}</li>
                </ul>
            </div>
        `;
        submitBtn.disabled = false;
    } else {
        container.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle"></i> File Invalid</h6>
                <p>${result.message}</p>
            </div>
        `;
        submitBtn.disabled = true;
    }
    
    container.classList.remove('d-none');
}

function showError(message) {
    document.getElementById('drop-zone').classList.add('error');
    document.getElementById('validation-results').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> ${message}
        </div>
    `;
    document.getElementById('validation-results').classList.remove('d-none');
    document.getElementById('submit-btn').disabled = true;
}

function validateForm() {
    const file = document.getElementById('file-input').files[0];
    if (!file) {
        showError('Please select a file');
        return false;
    }
    return true;
}

/*
function processImport() {
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    const progressContainer = document.getElementById('progress-container');
    
    // Show loading state
    submitText.classList.add('d-none');
    submitLoading.classList.remove('d-none');
    submitBtn.disabled = true;
    progressContainer.classList.remove('d-none');
    
    // Simulate progress (since we can't track real progress easily)
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 10;
        if (progress > 90) progress = 90;
        
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('progress-text').textContent = `Processing... ${Math.round(progress)}%`;
    }, 500);
    
    // Submit form
    const formData = new FormData(document.getElementById('import-form'));
    
    // Create AbortController with longer timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 5 * 60 * 1000); // 5 minutes
    
    fetch(document.getElementById('import-form').action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        signal: controller.signal
    }).then(response => {
        clearTimeout(timeoutId);
        clearInterval(interval);
        document.getElementById('progress-bar').style.width = '100%';
        document.getElementById('progress-text').textContent = 'Complete!';
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    }).then(data => {
        if (data.success) {
            // Show success message and redirect
            showSuccess(`Import berhasil! ${data.success_count} records imported.`);
            setTimeout(() => {
                window.location.href = '{{ route("admin.import.index") }}';
            }, 2000);
        } else {
            throw new Error(data.message || 'Import failed');
        }
    }).catch(error => {
        clearInterval(interval);
        console.error('Import error:', error);
        showError('Import failed: ' + error.message);
        
        // Reset form
        submitText.classList.remove('d-none');
        submitLoading.classList.add('d-none');
        submitBtn.disabled = false;
        progressContainer.classList.add('d-none');
    });
}
*/

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showSuccess(message) {
    // Create success alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alert, cardBody.firstChild);
}

function showError(message) {
    // Create error alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        <i class="fas fa-exclamation-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alert, cardBody.firstChild);
}
</script>
@endsection