<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\Dataset;
use App\Models\TrainingSession;
use App\Models\Prediction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics for dashboard cards
        $statistics = [
            'total_respondents' => Respondent::count(),
            'total_datasets' => Dataset::count(),
            'training_data_count' => Dataset::trainingData()->count(),
            'testing_data_count' => Dataset::testingData()->count(),
            'total_predictions' => Prediction::count(),
            'recent_predictions' => Prediction::recent()->count()
        ];

        // Get latest training session info
        $latestTrainingSession = TrainingSession::getLatestModel();
        $modelAccuracy = $latestTrainingSession ? $latestTrainingSession->accuracy : null;

        // Get classification distribution for charts
        $classificationDistribution = Dataset::selectRaw('label_intensitas, COUNT(*) as count')
            ->groupBy('label_intensitas')
            ->pluck('count', 'label_intensitas')
            ->toArray();

        // Get monthly predictions data for trends
        $monthlyPredictions = Prediction::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Get durasi penggunaan distribution
        $durasiDistribution = Dataset::selectRaw('durasi_penggunaan, COUNT(*) as count')
            ->groupBy('durasi_penggunaan')
            ->pluck('count', 'durasi_penggunaan')
            ->toArray();

        // Get frekuensi akses distribution
        $frekuensiDistribution = Dataset::selectRaw('frekuensi_akses, COUNT(*) as count')
            ->groupBy('frekuensi_akses')
            ->pluck('count', 'frekuensi_akses')
            ->toArray();

        return view('admin.dashboard', compact(
            'statistics',
            'latestTrainingSession',
            'modelAccuracy',
            'classificationDistribution',
            'monthlyPredictions',
            'durasiDistribution',
            'frekuensiDistribution'
        ));
    }

    /**
     * Get real-time statistics for AJAX requests
     */
    public function getStats()
    {
        return response()->json([
            'total_respondents' => Respondent::count(),
            'total_datasets' => Dataset::count(),
            'model_accuracy' => TrainingSession::getLatestModel()?->accuracy_percentage ?? 'N/A',
            'recent_predictions' => Prediction::recent()->count()
        ]);
    }
}
