<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RespondentController;
use App\Http\Controllers\Admin\DatasetController;
use App\Http\Controllers\Admin\TrainingController;
use App\Http\Controllers\Admin\PredictionController;
use App\Http\Controllers\Admin\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Respondent management
    Route::get('respondents/export', [RespondentController::class, 'export'])->name('respondents.export');
    Route::resource('respondents', RespondentController::class);
    
    // Dataset management
    Route::post('datasets/import', [DatasetController::class, 'import'])->name('datasets.import');
    Route::get('datasets/export', [DatasetController::class, 'export'])->name('datasets.export');  
    Route::resource('datasets', DatasetController::class);
    
    // Training management
    Route::get('training', [TrainingController::class, 'index'])->name('training.index');
    Route::get('training/create', [TrainingController::class, 'create'])->name('training.create');
    Route::post('training/train', [TrainingController::class, 'train'])->name('training.train');
    Route::post('training/cross-validate', [TrainingController::class, 'crossValidate'])->name('training.cross_validate');
    Route::get('training/{session}', [TrainingController::class, 'show'])->name('training.show');
    Route::delete('training/{session}', [TrainingController::class, 'destroy'])->name('training.destroy');
    Route::get('training/{session}/export', [TrainingController::class, 'export'])->name('training.export');
    Route::post('training/predict', [TrainingController::class, 'predict'])->name('training.predict');
    Route::get('training/api/statistics', [TrainingController::class, 'statistics'])->name('training.statistics');
    Route::get('training/api/feature-importance', [TrainingController::class, 'featureImportance'])->name('training.feature_importance');
    Route::post('training/api/compare', [TrainingController::class, 'compare'])->name('training.compare');
    
    // Prediction routes
    Route::get('prediction', [PredictionController::class, 'index'])->name('prediction.index');
    Route::post('prediction/predict', [PredictionController::class, 'predict'])->name('prediction.predict');
    Route::get('prediction/history', [PredictionController::class, 'history'])->name('prediction.history');
    Route::get('prediction/export', [PredictionController::class, 'export'])->name('prediction.export');
    Route::get('prediction/{id}', [PredictionController::class, 'show'])->name('prediction.show');
    Route::get('prediction/{id}/export', [PredictionController::class, 'exportSingle'])->name('prediction.export_single');
    Route::delete('prediction/{id}', [PredictionController::class, 'destroy'])->name('prediction.destroy');
    
    // Import routes
    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::get('import/upload', [ImportController::class, 'uploadForm'])->name('import.upload');
    Route::get('import/simple', [ImportController::class, 'simpleForm'])->name('import.simple');
    Route::post('import/process', [ImportController::class, 'processImport'])->name('import.process');
    Route::get('import/download-template', [ImportController::class, 'downloadTemplate'])->name('import.download-template');
    Route::post('import/validate', [ImportController::class, 'validateFile'])->name('import.validate');
    Route::get('import/history', [ImportController::class, 'getImportHistory'])->name('import.history');
    
    // Test routes  
    Route::get('import/test', [ImportController::class, 'testForm'])->name('import.test_form');
    Route::post('import/test', [ImportController::class, 'testUpload'])->name('import.test');
});

require __DIR__.'/auth.php';
