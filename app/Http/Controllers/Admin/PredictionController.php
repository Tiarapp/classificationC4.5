<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingSession;
use App\Models\PredictionResult;
use App\Services\TrainingService;
use App\Exports\PredictionResultsExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PredictionController extends Controller
{
    protected TrainingService $trainingService;

    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    /**
     * Show prediction form
     */
    public function index(): View
    {
        // Get the best trained model
        $bestModel = TrainingSession::orderBy('accuracy', 'desc')->first();
        $availableModels = TrainingSession::orderBy('created_at', 'desc')->get();
        
        return view('admin.prediction.index', compact('bestModel', 'availableModels'));
    }

    /**
     * Make a prediction
     */
    public function predict(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'model_id' => 'required|exists:training_sessions,id',
                'durasi_penggunaan' => 'required|integer|min:1|max:10',
                'frekuensi_akses' => 'required|integer|min:1|max:10', 
                'perhatian_konten' => 'required|integer|min:1|max:10',
                'penghayatan' => 'required|integer|min:1|max:10',
                'save_prediction' => 'nullable|in:on,1,true',
                'nama' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Get the selected model
            $model = TrainingSession::findOrFail($validated['model_id']);
            
            // Prepare input data (raw values from form)
            $inputData = [
                'durasi_penggunaan' => $validated['durasi_penggunaan'],
                'frekuensi_akses' => $validated['frekuensi_akses'],
                'perhatian_konten' => $validated['perhatian_konten'],
                'penghayatan' => $validated['penghayatan']
            ];
            
            // Convert to categorical values for prediction algorithm
            $categoricalInput = $this->convertToCategorical($inputData);

            // Make prediction using the trained model
            $modelData = $model->model_data; // Already casted to array
            
            if (!$modelData) {
                throw new \Exception('Model data tidak valid atau kosong');
            }
            
            // If model_data is still a string (double encoded), decode it
            if (is_string($modelData)) {
                $modelData = json_decode($modelData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Model data JSON tidak valid: ' . json_last_error_msg());
                }
            }
            
            // Handle different model data formats
            $prediction = null;
            
            // Format 1: New format with 'root' and 'children' (Decision Tree)
            if (isset($modelData['root']) && isset($modelData['children'])) {
                $prediction = $this->makePredictionDecisionTree($modelData, $categoricalInput);
            }
            // Format 2: Old format with 'tree' (Simple leaf or full tree)
            elseif (isset($modelData['tree'])) {
                $prediction = $this->makePredictionTree($modelData, $categoricalInput);
            } 
            else {
                throw new \Exception('Format model data tidak dikenali - tidak memiliki struktur yang valid');
            }
            
            if (!$prediction) {
                throw new \Exception('Gagal membuat prediksi dari model data');
            }
            
            // Calculate confidence (simplified for now)
            $confidence = 0.95; // Will be enhanced later
            
            // Save prediction if requested
            $savedPrediction = null;
            if ($request->input('save_prediction', false)) {
                $savedPrediction = $this->savePrediction([
                    'nama' => $validated['nama'] ?? null,
                    'durasi_penggunaan' => $categoricalInput['durasi_penggunaan'],
                    'frekuensi_akses' => $categoricalInput['frekuensi_akses'],
                    'perhatian_konten' => $inputData['perhatian_konten'],
                    'penghayatan' => $inputData['penghayatan'],
                    'predicted_label' => strtoupper($prediction),
                    'confidence_score' => $confidence,
                    'training_session_id' => $model->id,
                    'model_accuracy' => $model->accuracy,
                    'notes' => $validated['notes'] ?? null,
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'prediction' => $prediction,
                'confidence' => $confidence,
                'input_data' => $inputData,
                'categorical_data' => $categoricalInput,
                'saved' => $savedPrediction ? true : false,
                'saved_id' => $savedPrediction ? $savedPrediction->id : null,
                'model_info' => [
                    'id' => $model->id,
                    'algorithm' => $model->algorithm,
                    'accuracy' => $model->accuracy,
                    'created_at' => $model->created_at->format('d/m/Y H:i')
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
     * Make prediction using decision tree format (root + children)
     */
    private function makePredictionDecisionTree(array $tree, array $input): string
    {
        // Convert numeric inputs to categorical ranges for tree traversal
        $categoricalInput = $this->convertToCategorical($input);
        
        return $this->traverseTree($tree, $categoricalInput, $tree['root']);
    }
    
    /**
     * Make prediction using tree format (with tree key)
     */
    private function makePredictionTree(array $modelData, array $input): string
    {
        $tree = $modelData['tree'];
        
        // Use TrainingService for proper tree traversal instead of simplified logic
        $result = $this->trainingService->predictSingle($tree, $input);
        
        return $result['prediction'];
    }

    /**
     * Make prediction using simple tree format (legacy method name)
     */
    private function makePrediction(array $tree, array $input): string
    {
        // This method is kept for backward compatibility
        return $this->makePredictionDecisionTree($tree, $input);
    }

    /**
     * Convert numeric input to categorical ranges 
     */
    private function convertToCategorical(array $input): array
    {
        $categorical = [];
        
        // Convert durasi_penggunaan (1-10 scale to categories)
        $durasi = $input['durasi_penggunaan'];
        if ($durasi <= 2) {
            $categorical['durasi_penggunaan'] = '<=1 jam';
        } elseif ($durasi <= 5) {
            $categorical['durasi_penggunaan'] = '1-3 jam';
        } elseif ($durasi <= 8) {
            $categorical['durasi_penggunaan'] = '3-5 jam';
        } else {
            $categorical['durasi_penggunaan'] = '>5 jam';
        }
        
        // Convert frekuensi_akses (1-10 scale to categories)
        $frekuensi = $input['frekuensi_akses'];
        if ($frekuensi <= 3) {
            $categorical['frekuensi_akses'] = '1-2x';
        } elseif ($frekuensi <= 6) {
            $categorical['frekuensi_akses'] = '3-5x';
        } else {
            $categorical['frekuensi_akses'] = '>5x';
        }
        
        // Convert perhatian_konten (1-10 scale to 1-5 scale for decision tree compatibility)
        $perhatian = $input['perhatian_konten'];
        $categorical['perhatian_konten'] = min(5, max(1, round($perhatian / 2)));
        
        // Convert penghayatan (1-10 scale to 1-5 scale for decision tree compatibility)  
        $penghayatan = $input['penghayatan'];
        $categorical['penghayatan'] = min(5, max(1, round($penghayatan / 2)));
        
        return $categorical;
    }

    /**
     * Traverse the tree to make prediction
     */
    private function traverseTree(array $tree, array $input, string $currentAttribute): string
    {
        if (!isset($tree['children'])) {
            return 'rendah'; // Default if no children
        }
        
        $attributeValue = $input[$currentAttribute] ?? null;
        
        if (!isset($tree['children'][$attributeValue])) {
            // If value not found, return most common class (simplified)
            return 'sedang'; // Default prediction
        }
        
        $child = $tree['children'][$attributeValue];
        
        // If it's a leaf node (has label)
        if (isset($child['label'])) {
            return $child['label'];
        }
        
        // If it's an internal node (has split)
        if (isset($child['split'])) {
            return $this->traverseTree($child, $input, $child['split']);
        }
        
        return 'sedang'; // Default fallback
    }

    /**
     * Save prediction result to database
     */
    private function savePrediction(array $data): PredictionResult
    {
        return PredictionResult::create([
            'nama' => $data['nama'],
            'durasi_penggunaan' => $data['durasi_penggunaan'],
            'frekuensi_akses' => $data['frekuensi_akses'],
            'perhatian_konten' => $data['perhatian_konten'],
            'penghayatan' => $data['penghayatan'],
            'predicted_label' => $data['predicted_label'],
            'confidence_score' => $data['confidence_score'],
            'training_session_id' => $data['training_session_id'],
            'model_accuracy' => $data['model_accuracy'],
            'notes' => $data['notes'],
            'user_agent' => $data['user_agent'],
            'ip_address' => $data['ip_address']
        ]);
    }

    /**
     * Show prediction history
     */
    public function history(Request $request): View
    {
        $query = PredictionResult::with('trainingSession')
            ->orderBy('created_at', 'desc');
        
        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Filter by prediction label
        if ($request->filled('prediction_filter')) {
            $query->where('predicted_label', $request->prediction_filter);
        }
        
        $predictions = $query->paginate(15);
        $stats = PredictionResult::getStats();
        
        return view('admin.prediction.history', compact('predictions', 'stats'));
    }

    /**
     * Show single prediction detail
     */
    public function show($id): View
    {
        $prediction = PredictionResult::with('trainingSession')->findOrFail($id);
        
        return view('admin.prediction.show', compact('prediction'));
    }

    /**
     * Delete prediction result
     */
    public function destroy($id): JsonResponse
    {
        try {
            $prediction = PredictionResult::findOrFail($id);
            $prediction->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Hasil prediksi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus hasil prediksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export prediction results to Excel
     */
    public function export(Request $request)
    {
        $filters = [];
        
        // Apply filters from request
        if ($request->filled('start_date')) {
            $filters['start_date'] = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $filters['end_date'] = $request->end_date;
        }
        if ($request->filled('prediction_filter')) {
            $filters['prediction_filter'] = $request->prediction_filter;
        }
        
        $filename = 'prediction_results_' . date('Y-m-d_His') . '.xlsx';
        
        try {
            return Excel::download(new PredictionResultsExport($filters), $filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export single prediction result
     */
    public function exportSingle($id)
    {
        try {
            $prediction = PredictionResult::with('trainingSession')->findOrFail($id);
            
            // Create a collection with single item for export
            $collection = collect([$prediction]);
            
            $filename = 'prediction_' . $id . '_' . date('Y-m-d_His') . '.xlsx';
            
            // Create temporary export class for single item
            $export = new class($collection) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                protected $collection;
                
                public function __construct($collection) {
                    $this->collection = $collection;
                }
                
                public function collection() {
                    return $this->collection->map(function($prediction) {
                        return $prediction->toExportArray();
                    });
                }
                
                public function headings(): array {
                    return array_keys($this->collection->first()->toExportArray());
                }
            };
            
            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengexport data: ' . $e->getMessage()
            ], 500);
        }
    }
}
