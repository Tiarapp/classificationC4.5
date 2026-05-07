<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use App\Services\TrainingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TrainingController extends Controller
{
    protected TrainingService $trainingService;

    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    /**
     * Display training interface
     */
    public function index(): View
    {
        $recentSessions = TrainingSession::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $bestSession = TrainingSession::orderBy('accuracy', 'desc')
            ->first();

        $totalSessions = TrainingSession::count();
        $avgAccuracy = TrainingSession::avg('accuracy') ?? 0;

        return view('admin.training.index', compact(
            'recentSessions',
            'bestSession', 
            'totalSessions',
            'avgAccuracy'
        ));
    }

    /**
     * Show training form
     */
    public function create(): View
    {
        return view('admin.training.create');
    }

    /**
     * Start training process
     */
    public function train(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'train_ratio' => 'numeric|min:0.1|max:0.9',
                'max_depth' => 'integer|min:1|max:20',
                'min_samples_leaf' => 'integer|min:1|max:20',
                'enable_pruning' => 'boolean'
            ]);

            $parameters = [
                'train_ratio' => $validated['train_ratio'] ?? 0.7,
                'max_depth' => $validated['max_depth'] ?? 10,
                'min_samples_leaf' => $validated['min_samples_leaf'] ?? 2,
                'enable_pruning' => $validated['enable_pruning'] ?? true
            ];

            $result = $this->trainingService->trainModel($parameters);

            return response()->json([
                'success' => true,
                'message' => 'Model berhasil dilatih!',
                'data' => [
                    'session_id' => $result['session_id'],
                    'accuracy' => round($result['test_metrics']['accuracy'] * 100, 2),
                    'training_time' => round($result['training_time'], 2),
                    'tree_stats' => $result['tree_stats'],
                    'data_split' => $result['data_split']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform cross validation
     */
    public function crossValidate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'k' => 'integer|min:2|max:10',
                'max_depth' => 'integer|min:1|max:20',
                'min_samples_leaf' => 'integer|min:1|max:20'
            ]);

            $k = $validated['k'] ?? 5;
            $parameters = [
                'max_depth' => $validated['max_depth'] ?? 10,
                'min_samples_leaf' => $validated['min_samples_leaf'] ?? 2
            ];

            $result = $this->trainingService->crossValidate($k, $parameters);

            return response()->json([
                'success' => true,
                'message' => "{$k}-fold cross validation selesai!",
                'data' => [
                    'average_accuracy' => round($result['average_accuracy'] * 100, 2),
                    'std_accuracy' => round($result['std_accuracy'] * 100, 2),
                    'folds' => $result['folds']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show training session details
     */
    public function show(TrainingSession $session): View
    {
        $modelData = json_decode($session->model_data, true);
        
        // Generate gain analysis for display
        $gainAnalysis = null;
        if (isset($modelData['tree'])) {
            $c45Service = app(\App\Services\C45AlgorithmService::class);
            $attributes = ['durasi_penggunaan', 'frekuensi_akses', 'perhatian_konten', 'penghayatan'];
            
            // Get original data for analysis (using same parameters as training)
            $allData = \App\Models\Dataset::all();
            $trainingService = app(\App\Services\TrainingService::class);
            $preparedData = $trainingService->prepareData($allData);
            
            // Recreate the same data split used in training
            $parameters = json_decode($session->parameters, true) ?? [];
            $trainRatio = $parameters['train_ratio'] ?? 0.7;
            $seed = $parameters['random_seed'] ?? ($allData->count() * 42);
            $split = $trainingService->splitData($preparedData, $trainRatio, $seed);
            
            $gainAnalysis = $c45Service->analyzeDecisionTree(
                $modelData['tree'], 
                $split['train'], 
                $attributes,
                0, // Initial depth
                []  // Initial empty path
            );
        }
        
        return view('admin.training.show', compact('session', 'modelData', 'gainAnalysis'));
    }

    /**
     * Get feature importance
     */
    public function featureImportance(): JsonResponse
    {
        try {
            $data = \App\Models\Dataset::all();
            $preparedData = $this->trainingService->prepareData($data);
            $importance = $this->trainingService->getFeatureImportance($preparedData);

            return response()->json([
                'success' => true,
                'data' => $importance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare training sessions
     */
    public function compare(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_ids' => 'required|array|min:2',
                'session_ids.*' => 'exists:training_sessions,id'
            ]);

            $comparisons = $this->trainingService->compareModels($validated['session_ids']);

            return response()->json([
                'success' => true,
                'data' => $comparisons
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export trained model
     */
    public function export(TrainingSession $session): JsonResponse
    {
        try {
            $model = $this->trainingService->exportModel($session->id);

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $model
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete training session
     */
    public function destroy(TrainingSession $session): RedirectResponse
    {
        try {
            $session->delete();
            
            return redirect()->route('admin.training.index')
                ->with('success', 'Sesi training berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus sesi training: ' . $e->getMessage());
        }
    }

    /**
     * Get training statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_sessions' => TrainingSession::count(),
                'avg_accuracy' => TrainingSession::avg('accuracy') ?? 0,
                'best_accuracy' => TrainingSession::max('accuracy') ?? 0,
                'avg_training_time' => TrainingSession::avg('training_time') ?? 0,
                'recent_sessions' => TrainingSession::orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(['id', 'accuracy', 'training_time', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test prediction with trained model
     */
    public function predict(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|exists:training_sessions,id',
                'durasi_penggunaan' => 'required|in:<=1 jam,1-3 jam,3-5 jam,>5 jam',
                'frekuensi_akses' => 'required|in:1-2x,3-5x,>5x',
                'perhatian_konten' => 'required|integer|min:1|max:5',
                'penghayatan' => 'required|integer|min:1|max:5'
            ]);

            $summary = $this->trainingService->getModelSummary($validated['session_id']);
            
            if (!$summary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model tidak ditemukan'
                ], 404);
            }

            $instance = [
                'durasi_penggunaan' => $validated['durasi_penggunaan'],
                'frekuensi_akses' => $validated['frekuensi_akses'],
                'perhatian_konten' => $validated['perhatian_konten'],
                'penghayatan' => $validated['penghayatan']
            ];

            $prediction = $this->trainingService->predictSingle($summary['tree'], $instance);

            return response()->json([
                'success' => true,
                'data' => [
                    'prediction' => $prediction['prediction'],
                    'confidence' => round($prediction['confidence'] * 100, 2),
                    'input' => $prediction['input'],
                    'model_accuracy' => round($summary['session']->accuracy * 100, 2)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}