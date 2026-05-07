<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TrainingService
{
    protected C45AlgorithmService $c45Service;

    public function __construct(C45AlgorithmService $c45Service)
    {
        $this->c45Service = $c45Service;
    }

    /**
     * Split data into training and testing sets
     */
    public function splitData(Collection $data, float $trainRatio = 0.7, ?int $seed = null): array
    {
        // Set random seed for reproducible results
        if ($seed !== null) {
            srand($seed);
        } else {
            // Use a fixed seed based on data count for consistency
            srand($data->count() * 42);
        }
        
        // Stratified split to maintain class distribution
        $classes = $data->groupBy('label_intensitas');
        $trainData = collect();
        $testData = collect();

        foreach ($classes as $class => $classData) {
            // Sort by ID first for deterministic base order
            $sorted = $classData->sortBy('id');
            
            // Create deterministic shuffle using seed-based index permutation
            $items = $sorted->values()->toArray();
            $count = count($items);
            
            // Generate deterministic permutation based on current seed state
            for ($i = $count - 1; $i > 0; $i--) {
                $j = rand(0, $i);
                $temp = $items[$i];
                $items[$i] = $items[$j];
                $items[$j] = $temp;
            }
            
            $shuffled = collect($items);
            $trainSize = (int)($shuffled->count() * $trainRatio);
            
            $trainData = $trainData->merge($shuffled->take($trainSize));
            $testData = $testData->merge($shuffled->skip($trainSize));
        }

        return [
            'train' => $trainData->sortBy('id'), // Sort by ID for consistency
            'test' => $testData->sortBy('id')    // Sort by ID for consistency
        ];
    }

    /**
     * Prepare dataset for training (convert to array format)
     */
    public function prepareData(Collection $datasets): Collection
    {
        return $datasets->map(function ($dataset) {
            return [
                'id' => $dataset->id,
                'respondent_id' => $dataset->respondent_id,
                'durasi_penggunaan' => $dataset->durasi_penggunaan,
                'frekuensi_akses' => $dataset->frekuensi_akses,
                'perhatian_konten' => $dataset->perhatian_konten,
                'penghayatan' => $dataset->penghayatan,
                'label_intensitas' => $dataset->label_intensitas,
            ];
        });
    }

    /**
     * Train C4.5 model with cross-validation
     */
    public function trainModel(array $parameters = []): array
    {
        $startTime = microtime(true);
        
        // Get all datasets
        $allData = Dataset::with('respondent')->get();
        
        if ($allData->count() < 10) {
            throw new \Exception('Minimal 10 data diperlukan untuk training model');
        }

        // Prepare data
        $preparedData = $this->prepareData($allData);
        
        // Split data with consistent seed for reproducible results
        $trainRatio = $parameters['train_ratio'] ?? 0.7;
        $seed = $parameters['random_seed'] ?? ($allData->count() * 42); // Consistent seed
        $split = $this->splitData($preparedData, $trainRatio, $seed);
        
        $trainData = $split['train'];
        $testData = $split['test'];

        // Define attributes for decision tree
        $attributes = [
            'durasi_penggunaan',
            'frekuensi_akses', 
            'perhatian_konten',
            'penghayatan'
        ];

        // Training parameters
        $minSamplesLeaf = $parameters['min_samples_leaf'] ?? 2;
        $maxDepth = $parameters['max_depth'] ?? 10;
        $enablePruning = $parameters['enable_pruning'] ?? true;

        // Build decision tree
        Log::info("Building decision tree with {$trainData->count()} training samples");
        
        $tree = $this->c45Service->buildDecisionTree(
            $trainData,
            $attributes,
            $minSamplesLeaf,
            $maxDepth
        );

        // Post-pruning if enabled
        if ($enablePruning && $testData->count() > 0) {
            Log::info("Applying post-pruning");
            $tree = $this->c45Service->pruneTree($tree, $testData);
        }

        // Evaluate model
        $trainMetrics = $this->c45Service->evaluateModel($tree, $trainData);
        $testMetrics = $this->c45Service->evaluateModel($tree, $testData);

        // Generate rules
        $rules = $this->c45Service->treeToRules($tree);
        
        // Get tree statistics
        $treeStats = $this->c45Service->getTreeStatistics($tree);

        $endTime = microtime(true);
        $trainingTime = $endTime - $startTime;

        // Save training session
        $session = TrainingSession::create([
            'algorithm' => 'C4.5',
            'parameters' => json_encode($parameters),
            'train_data_count' => $trainData->count(),
            'test_data_count' => $testData->count(),
            'accuracy' => $testMetrics['accuracy'],
            'training_time' => $trainingTime,
            'model_data' => json_encode([
                'tree' => $tree,
                'attributes' => $attributes,
                'train_metrics' => $trainMetrics,
                'test_metrics' => $testMetrics,
                'rules' => $rules,
                'tree_stats' => $treeStats
            ])
        ]);

        return [
            'session_id' => $session->id,
            'tree' => $tree,
            'attributes' => $attributes,
            'train_metrics' => $trainMetrics,
            'test_metrics' => $testMetrics,
            'rules' => $rules,
            'tree_stats' => $treeStats,
            'training_time' => $trainingTime,
            'data_split' => [
                'total' => $allData->count(),
                'train' => $trainData->count(),
                'test' => $testData->count(),
                'train_ratio' => $trainRatio
            ]
        ];
    }

    /**
     * Perform k-fold cross validation
     */
    public function crossValidate(int $k = 5, array $parameters = []): array
    {
        $allData = Dataset::with('respondent')->get();
        $preparedData = $this->prepareData($allData);
        
        if ($preparedData->count() < $k) {
            throw new \Exception("Data tidak cukup untuk {$k}-fold cross validation");
        }

        // Use deterministic shuffle for cross validation
        $seed = $parameters['random_seed'] ?? ($allData->count() * 42);
        srand($seed);
        
        // Create deterministic shuffle
        $items = $preparedData->values()->toArray();
        $count = count($items);
        
        for ($i = $count - 1; $i > 0; $i--) {
            $j = rand(0, $i);
            $temp = $items[$i];
            $items[$i] = $items[$j];
            $items[$j] = $temp;
        }
        
        $shuffled = collect($items);
        $foldSize = (int)($shuffled->count() / $k);
        $results = [];

        $attributes = [
            'durasi_penggunaan',
            'frekuensi_akses',
            'perhatian_konten',
            'penghayatan'
        ];

        for ($i = 0; $i < $k; $i++) {
            $start = $i * $foldSize;
            $end = ($i === $k - 1) ? $shuffled->count() : ($i + 1) * $foldSize;
            
            $testFold = $shuffled->slice($start, $end - $start);
            $trainFold = $shuffled->slice(0, $start)->merge($shuffled->slice($end));

            // Build tree for this fold
            $tree = $this->c45Service->buildDecisionTree(
                $trainFold,
                $attributes,
                $parameters['min_samples_leaf'] ?? 2,
                $parameters['max_depth'] ?? 10
            );

            // Evaluate fold
            $metrics = $this->c45Service->evaluateModel($tree, $testFold);
            $results[] = [
                'fold' => $i + 1,
                'train_size' => $trainFold->count(),
                'test_size' => $testFold->count(),
                'accuracy' => $metrics['accuracy'],
                'metrics' => $metrics
            ];
        }

        // Calculate average metrics
        $avgAccuracy = collect($results)->avg('accuracy');
        $stdAccuracy = $this->calculateStandardDeviation(
            collect($results)->pluck('accuracy')->toArray()
        );

        return [
            'k' => $k,
            'folds' => $results,
            'average_accuracy' => $avgAccuracy,
            'std_accuracy' => $stdAccuracy,
            'total_samples' => $preparedData->count()
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $sumSquares = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values));
        return sqrt($sumSquares / count($values));
    }

    /**
     * Get feature importance based on information gain
     */
    public function getFeatureImportance(Collection $data): array
    {
        $attributes = [
            'durasi_penggunaan',
            'frekuensi_akses',
            'perhatian_konten',
            'penghayatan'
        ];

        $importance = [];
        foreach ($attributes as $attribute) {
            $importance[$attribute] = $this->c45Service->calculateInformationGain($data, $attribute);
        }

        // Sort by importance
        arsort($importance);
        
        return $importance;
    }

    /**
     * Predict single instance
     */
    public function predictSingle(array $tree, array $instance): array
    {
        $prediction = $this->c45Service->predict($tree, $instance);
        
        // Calculate confidence (simplified)
        $confidence = $this->calculatePredictionConfidence($tree, $instance);
        
        return [
            'prediction' => $prediction,
            'confidence' => $confidence,
            'input' => $instance
        ];
    }

    /**
     * Calculate prediction confidence
     */
    private function calculatePredictionConfidence(array $tree, array $instance): float
    {
        if ($tree['type'] === 'leaf') {
            return $tree['confidence'] ?? 1.0;
        }

        $attributeValue = $instance[$tree['attribute']] ?? null;
        
        if (!isset($tree['children'][$attributeValue])) {
            return 0.5; // Low confidence for unknown values
        }

        return $this->calculatePredictionConfidence($tree['children'][$attributeValue], $instance);
    }

    /**
     * Get model summary
     */
    public function getModelSummary(int $sessionId): ?array
    {
        $session = TrainingSession::find($sessionId);
        
        if (!$session) {
            return null;
        }

        $modelData = json_decode($session->model_data, true);
        
        return [
            'session' => $session,
            'tree' => $modelData['tree'],
            'attributes' => $modelData['attributes'],
            'train_metrics' => $modelData['train_metrics'],
            'test_metrics' => $modelData['test_metrics'],
            'rules' => $modelData['rules'],
            'tree_stats' => $modelData['tree_stats']
        ];
    }

    /**
     * Compare multiple models
     */
    public function compareModels(array $sessionIds): array
    {
        $comparisons = [];
        
        foreach ($sessionIds as $sessionId) {
            $session = TrainingSession::find($sessionId);
            if ($session) {
                $modelData = json_decode($session->model_data, true);
                $comparisons[] = [
                    'session_id' => $sessionId,
                    'algorithm' => $session->algorithm,
                    'accuracy' => $session->accuracy,
                    'train_data_count' => $session->train_data_count,
                    'test_data_count' => $session->test_data_count,
                    'training_time' => $session->training_time,
                    'tree_stats' => $modelData['tree_stats'] ?? [],
                    'created_at' => $session->created_at
                ];
            }
        }

        // Sort by accuracy
        usort($comparisons, fn($a, $b) => $b['accuracy'] <=> $a['accuracy']);
        
        return $comparisons;
    }

    /**
     * Export model for deployment
     */
    public function exportModel(int $sessionId): ?array
    {
        $summary = $this->getModelSummary($sessionId);
        
        if (!$summary) {
            return null;
        }

        return [
            'model_version' => '1.0',
            'algorithm' => 'C4.5',
            'created_at' => $summary['session']->created_at,
            'tree' => $summary['tree'],
            'attributes' => $summary['attributes'],
            'accuracy' => $summary['session']->accuracy,
            'training_samples' => $summary['session']->train_data_count,
            'metadata' => [
                'session_id' => $sessionId,
                'training_time' => $summary['session']->training_time,
                'tree_statistics' => $summary['tree_stats']
            ]
        ];
    }
}