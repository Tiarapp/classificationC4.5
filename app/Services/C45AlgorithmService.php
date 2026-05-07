<?php

namespace App\Services;

use App\Models\Dataset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class C45AlgorithmService
{
    /**
     * Calculate entropy of a dataset
     */
    public function calculateEntropy(Collection $data): float
    {
        if ($data->isEmpty()) {
            return 0;
        }

        // Count occurrences of each class label
        $classCounts = $data->groupBy('label_intensitas')->map->count();
        $total = $data->count();
        
        $entropy = 0;
        
        // Sort class counts for consistency
        $sortedClassCounts = $classCounts->sortKeys();
        
        foreach ($sortedClassCounts as $count) {
            if ($count > 0) {
                $probability = $count / $total;
                $entropy -= $probability * log($probability, 2);
            }
        }

        return $entropy;
    }

    /**
     * Calculate information gain for an attribute
     */
    public function calculateInformationGain(Collection $data, string $attribute): float
    {
        if ($data->isEmpty()) {
            return 0;
        }
        
        $totalEntropy = $this->calculateEntropy($data);
        $totalCount = $data->count();

        
        // Group data by attribute values and sort by key for consistency
        $groups = $data->groupBy($attribute)->sortKeys();
        
        $weightedEntropy = 0;
        foreach ($groups as $value => $group) {
            $weight = $group->count() / $totalCount;
            $entropy = $this->calculateEntropy($group);
            $weightedEntropy += $weight * $entropy;
        }

        return round($totalEntropy - $weightedEntropy, 10); // Round to avoid floating point issues
    }

    /**
     * Calculate gain ratio (C4.5 improvement over ID3)
     */
    public function calculateGainRatio(Collection $data, string $attribute): float
    {
        $informationGain = $this->calculateInformationGain($data, $attribute);
        
        if (abs($informationGain) < 1e-10) { // Use epsilon comparison instead of exact zero
            return 0;
        }

        // Calculate split information with sorted groups for consistency
        $groups = $data->groupBy($attribute)->sortKeys();
        $totalCount = $data->count();
        $splitInfo = 0;

        foreach ($groups as $group) {
            if ($group->count() > 0) {
                $probability = $group->count() / $totalCount;
                $splitInfo -= $probability * log($probability, 2);
            }
        }

        // Avoid division by zero with epsilon comparison
        if (abs($splitInfo) < 1e-10) {
            return 0;
        }

        return round($informationGain / $splitInfo, 10); // Round to avoid floating point issues
    }

    /**
     * Find the best attribute to split on using gain ratio
     */
    public function findBestAttribute(Collection $data, array $attributes): ?string
    {
        $bestAttribute = null;
        $bestGainRatio = -1;
        $gainComparison = [];
        
        // Sort attributes for consistent processing order
        sort($attributes);

        foreach ($attributes as $attribute) {
            $gainRatio = $this->calculateGainRatio($data, $attribute);
            $informationGain = $this->calculateInformationGain($data, $attribute);
            
            // Store comparison data
            $gainComparison[$attribute] = [
                'information_gain' => $informationGain,
                'gain_ratio' => $gainRatio,
                'selected' => false
            ];
            
            // Use epsilon comparison for floating point
            if ($gainRatio > $bestGainRatio + 1e-10) {
                $bestGainRatio = $gainRatio;
                $bestAttribute = $attribute;
            }
        }
        
        // Mark the selected attribute
        if ($bestAttribute) {
            $gainComparison[$bestAttribute]['selected'] = true;
        }
        
        // Log gain comparison for this node
        Log::info('🎯 GAIN COMPARISON - Node Analysis', [
            'samples' => $data->count(),
            'class_distribution' => $data->groupBy('label_intensitas')->map->count()->toArray(),
            'best_attribute' => $bestAttribute,
            'best_gain_ratio' => $bestGainRatio,
            'all_attributes' => $gainComparison
        ]);

        return $bestAttribute;
    }

    /**
     * Check if all data instances have the same class label
     */
    public function isPure(Collection $data): bool
    {
        if ($data->isEmpty()) {
            return true;
        }

        $firstLabel = $data->first()['label_intensitas'];
        return $data->every(fn($item) => $item['label_intensitas'] === $firstLabel);
    }

    /**
     * Get the majority class in the dataset
     */
    public function getMajorityClass(Collection $data): string
    {
        if ($data->isEmpty()) {
            return 'rendah'; // Default class
        }

        $classCounts = $data->groupBy('label_intensitas')->map->count();
        return $classCounts->keys()->sortByDesc(fn($key) => $classCounts[$key])->first();
    }

    /**
     * Build decision tree using C4.5 algorithm
     */
    public function buildDecisionTree(Collection $data, array $attributes, int $minSamplesLeaf = 2, int $maxDepth = 10, int $currentDepth = 0): array
    {
        // Base cases
        if ($data->isEmpty()) {
            return [
                'type' => 'leaf',
                'class' => 'rendah',
                'confidence' => 0,
                'samples' => 0
            ];
        }

        if ($this->isPure($data) || empty($attributes) || $currentDepth >= $maxDepth || $data->count() < $minSamplesLeaf) {
            $majorityClass = $this->getMajorityClass($data);
            $classCounts = $data->groupBy('label_intensitas')->map->count();
            $confidence = ($classCounts[$majorityClass] ?? 0) / $data->count();
            
            return [
                'type' => 'leaf',
                'class' => $majorityClass,
                'confidence' => $confidence,
                'samples' => $data->count(),
                'class_distribution' => $classCounts->toArray()
            ];
        }

        // Find best attribute to split on
        $bestAttribute = $this->findBestAttribute($data, $attributes);
        
        if ($bestAttribute === null) {
            $majorityClass = $this->getMajorityClass($data);
            $classCounts = $data->groupBy('label_intensitas')->map->count();
            $confidence = ($classCounts[$majorityClass] ?? 0) / $data->count();
            
            return [
                'type' => 'leaf',
                'class' => $majorityClass,
                'confidence' => $confidence,
                'samples' => $data->count(),
                'class_distribution' => $classCounts->toArray()
            ];
        }

        // Create internal node
        $node = [
            'type' => 'internal',
            'attribute' => $bestAttribute,
            'samples' => $data->count(),
            'entropy' => $this->calculateEntropy($data),
            'gain_ratio' => $this->calculateGainRatio($data, $bestAttribute),
            'children' => []
        ];

        // Create child nodes for each attribute value
        $groups = $data->groupBy($bestAttribute);
        $remainingAttributes = array_filter($attributes, fn($attr) => $attr !== $bestAttribute);

        foreach ($groups as $value => $subset) {
            $node['children'][$value] = $this->buildDecisionTree(
                $subset,
                $remainingAttributes,
                $minSamplesLeaf,
                $maxDepth,
                $currentDepth + 1
            );
        }

        return $node;
    }

    /**
     * Prune decision tree using post-pruning (reduced error pruning)
     */
    public function pruneTree(array $tree, Collection $validationData): array
    {
        if ($tree['type'] === 'leaf') {
            return $tree;
        }

        // Recursively prune child nodes
        foreach ($tree['children'] as $value => $child) {
            $childData = $validationData->filter(fn($item) => $item[$tree['attribute']] === $value);
            $tree['children'][$value] = $this->pruneTree($child, $childData);
        }

        // Calculate error before pruning
        $errorBefore = $this->calculateError($tree, $validationData);

        // Create leaf node with majority class
        $majorityClass = $this->getMajorityClass($validationData);
        $leafNode = [
            'type' => 'leaf',
            'class' => $majorityClass,
            'confidence' => 1.0,
            'samples' => $validationData->count()
        ];

        // Calculate error after pruning (converting to leaf)
        $errorAfter = $this->calculateError($leafNode, $validationData);

        // If pruning reduces error, return leaf node
        if ($errorAfter <= $errorBefore) {
            return $leafNode;
        }

        return $tree;
    }

    /**
     * Calculate prediction error on validation data
     */
    public function calculateError(array $tree, Collection $data): float
    {
        if ($data->isEmpty()) {
            return 0;
        }

        $errors = 0;
        foreach ($data as $instance) {
            $prediction = $this->predict($tree, $instance);
            if ($prediction !== $instance['label_intensitas']) {
                $errors++;
            }
        }

        return $errors / $data->count();
    }

    /**
     * Make prediction for a single instance
     */
    public function predict(array $tree, array $instance): string
    {
        if ($tree['type'] === 'leaf') {
            return $tree['class'];
        }

        $attributeValue = $instance[$tree['attribute']] ?? null;
        
        // If attribute value not found in tree, return majority class from training
        if (!isset($tree['children'][$attributeValue])) {
            // Return the most common class from all children
            $classes = [];
            foreach ($tree['children'] as $child) {
                if ($child['type'] === 'leaf') {
                    $classes[] = $child['class'];
                }
            }
            
            if (empty($classes)) {
                return 'rendah'; // Default
            }
            
            $classCounts = array_count_values($classes);
            return array_search(max($classCounts), $classCounts);
        }

        return $this->predict($tree['children'][$attributeValue], $instance);
    }

    /**
     * Evaluate model performance
     */
    public function evaluateModel(array $tree, Collection $testData): array
    {
        if ($testData->isEmpty()) {
            return [
                'accuracy' => 0,
                'precision' => [],
                'recall' => [],
                'f1_score' => [],
                'confusion_matrix' => [],
                'total_samples' => 0
            ];
        }

        $predictions = [];
        $actuals = [];
        
        foreach ($testData as $instance) {
            $predictions[] = $this->predict($tree, $instance);
            $actuals[] = $instance['label_intensitas'];
        }

        return $this->calculateMetrics($predictions, $actuals);
    }

    /**
     * Calculate performance metrics
     */
    private function calculateMetrics(array $predictions, array $actuals): array
    {
        $classes = ['rendah', 'sedang', 'tinggi'];
        $confusionMatrix = [];
        
        // Initialize confusion matrix
        foreach ($classes as $actual) {
            foreach ($classes as $predicted) {
                $confusionMatrix[$actual][$predicted] = 0;
            }
        }

        // Fill confusion matrix
        for ($i = 0; $i < count($predictions); $i++) {
            $actual = $actuals[$i];
            $predicted = $predictions[$i];
            $confusionMatrix[$actual][$predicted]++;
        }

        // Calculate metrics
        $correct = 0;
        $total = count($predictions);
        
        for ($i = 0; $i < count($predictions); $i++) {
            if ($predictions[$i] === $actuals[$i]) {
                $correct++;
            }
        }

        $accuracy = $total > 0 ? $correct / $total : 0;

        // Calculate precision, recall, F1 for each class
        $precision = [];
        $recall = [];
        $f1Score = [];

        foreach ($classes as $class) {
            $tp = $confusionMatrix[$class][$class];
            $fp = array_sum(array_column($confusionMatrix, $class)) - $tp;
            $fn = array_sum($confusionMatrix[$class]) - $tp;

            $precision[$class] = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0;
            $recall[$class] = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0;
            $f1Score[$class] = ($precision[$class] + $recall[$class]) > 0 
                ? 2 * ($precision[$class] * $recall[$class]) / ($precision[$class] + $recall[$class]) 
                : 0;
        }

        return [
            'accuracy' => $accuracy,
            'precision' => $precision,
            'recall' => $recall,
            'f1_score' => $f1Score,
            'confusion_matrix' => $confusionMatrix,
            'total_samples' => $total,
            'correct_predictions' => $correct
        ];
    }

    /**
     * Convert tree to human-readable rules
     */
    public function treeToRules(array $tree, array $conditions = []): array
    {
        if ($tree['type'] === 'leaf') {
            return [[
                'conditions' => $conditions,
                'class' => $tree['class'],
                'confidence' => $tree['confidence'],
                'samples' => $tree['samples']
            ]];
        }

        $rules = [];
        foreach ($tree['children'] as $value => $child) {
            $newConditions = $conditions;
            $newConditions[] = $tree['attribute'] . ' = ' . $value;
            $rules = array_merge($rules, $this->treeToRules($child, $newConditions));
        }

        return $rules;
    }

    /**
     * Get tree statistics
     */
    public function getTreeStatistics(array $tree): array
    {
        $stats = [
            'total_nodes' => 0,
            'leaf_nodes' => 0,
            'internal_nodes' => 0,
            'max_depth' => 0,
            'attributes_used' => []
        ];

        $this->collectTreeStats($tree, $stats, 0);

        return $stats;
    }

    /**
     * Analyze decision tree with detailed gain information
     */
    public function analyzeDecisionTree(array $tree, Collection $originalData, array $allAttributes, int $depth = 0, array $path = []): array
    {
        $analysis = [
            'depth' => $depth,
            'type' => $tree['type'],
            'samples' => $tree['samples'] ?? 0
        ];

        if ($tree['type'] === 'leaf') {
            $analysis['class'] = $tree['class'];
            $analysis['confidence'] = $tree['confidence'] ?? 0;
            $analysis['class_distribution'] = $tree['class_distribution'] ?? [];
        } else {
            // Internal node analysis
            $analysis['split_attribute'] = $tree['attribute'];
            $analysis['entropy'] = $tree['entropy'] ?? 0;
            $analysis['gain_ratio'] = $tree['gain_ratio'] ?? 0;
            
            // Get the data subset for this specific node based on the path
            $nodeData = $this->getNodeDataByPath($originalData, $path);
            
            // Add class distribution for detailed entropy calculation
            if (!$nodeData->isEmpty()) {
                $classDistribution = $nodeData->groupBy('label_intensitas')->map->count()->toArray();
                $analysis['class_distribution'] = $classDistribution;
            }
            
            // Calculate gain comparison for all attributes at this node
            $remainingAttributes = array_filter($allAttributes, fn($attr) => $attr !== $tree['attribute']);
            
            $gainComparison = [];
            if (!$nodeData->isEmpty()) {
                foreach ($allAttributes as $attribute) {
                    $informationGain = $this->calculateInformationGain($nodeData, $attribute);
                    $gainRatio = $this->calculateGainRatio($nodeData, $attribute);
                    
                    // Calculate detailed split information for this attribute
                    $splitDetails = $this->calculateSplitDetails($nodeData, $attribute);
                    
                    $gainComparison[$attribute] = [
                        'information_gain' => round($informationGain, 6),
                        'gain_ratio' => round($gainRatio, 6),
                        'selected' => $attribute === $tree['attribute'],
                        'split_details' => $splitDetails
                    ];
                }
            }
            
            $analysis['gain_comparison'] = $gainComparison;
            
            // Analyze children
            $analysis['children'] = [];
            if (isset($tree['children'])) {
                foreach ($tree['children'] as $value => $child) {
                    $childPath = array_merge($path, [['attribute' => $tree['attribute'], 'value' => $value]]);
                    $analysis['children'][$value] = $this->analyzeDecisionTree(
                        $child, 
                        $originalData, 
                        $allAttributes, 
                        $depth + 1,
                        $childPath
                    );
                }
            }
        }

        return $analysis;
    }

    /**
     * Get data subset for a specific node based on path from root
     */
    private function getNodeDataByPath(Collection $originalData, array $path): Collection
    {
        $filteredData = $originalData;
        
        foreach ($path as $pathStep) {
            $attribute = $pathStep['attribute'];
            $value = $pathStep['value'];
            
            $filteredData = $filteredData->filter(function ($item) use ($attribute, $value) {
                return $item[$attribute] == $value;
            });
        }
        
        return $filteredData;
    }

    /**
     * Calculate detailed split information for an attribute
     */
    private function calculateSplitDetails(Collection $data, string $attribute): array
    {
        $total = $data->count();
        $groups = $data->groupBy($attribute);
        $splitDetails = [];
        $weightedEntropy = 0;
        $splitInfo = 0;

        foreach ($groups as $value => $subset) {
            $subsetCount = $subset->count();
            $proportion = $subsetCount / $total;
            
            // Calculate entropy for this subset
            $classDistribution = $subset->groupBy('label_intensitas')->map->count()->toArray();
            $subsetEntropy = 0;
            $entropyParts = [];
            
            // Debug log untuk melihat distribusi kelas
            Log::info("Split details for {$attribute} = {$value}", [
                'subset_count' => $subsetCount,
                'class_distribution' => $classDistribution
            ]);
            
            foreach ($classDistribution as $class => $count) {
                if ($count > 0 && $subsetCount > 0) {
                    $classProb = $count / $subsetCount;
                    if ($classProb > 0) {
                        $logValue = log($classProb, 2);
                        $entropyContribution = -$classProb * $logValue;
                        $subsetEntropy += $entropyContribution;
                        
                        $entropyParts[] = [
                            'class' => $class,
                            'count' => $count,
                            'proportion' => $classProb,
                            'log_value' => $logValue,
                            'entropy_part' => $entropyContribution
                        ];
                    }
                }
            }
            
            $weightedEntropy += $proportion * $subsetEntropy;
            if ($proportion > 0) {
                $splitInfo += -$proportion * log($proportion, 2);
            }
            
            $splitDetails[$value] = [
                'count' => $subsetCount,
                'proportion' => $proportion,
                'entropy' => $subsetEntropy,
                'weighted_contribution' => $proportion * $subsetEntropy,
                'class_distribution' => $classDistribution,
                'entropy_calculation' => $entropyParts
            ];
            
            // Log hasil entropy
            Log::info("Calculated entropy for {$attribute} = {$value}: {$subsetEntropy}");
        }

        return [
            'groups' => $splitDetails,
            'total_weighted_entropy' => $weightedEntropy,
            'split_info' => $splitInfo,
            'total_samples' => $total
        ];
    }

    /**
     * Display gain comparison in a formatted way
     */
    public function displayGainComparison(array $treeAnalysis, int $depth = 0): string
    {
        $indent = str_repeat('  ', $depth);
        $output = '';
        
        if ($treeAnalysis['type'] === 'leaf') {
            $output .= $indent . "🍃 LEAF: {$treeAnalysis['class']} ";
            $output .= "(confidence: " . round($treeAnalysis['confidence'], 2) . ", ";
            $output .= "samples: {$treeAnalysis['samples']})\n";
        } else {
            $output .= $indent . "🔀 SPLIT on '{$treeAnalysis['split_attribute']}' ";
            $output .= "(samples: {$treeAnalysis['samples']}, ";
            $output .= "entropy: " . round($treeAnalysis['entropy'], 4) . ")\n";
            
            if (isset($treeAnalysis['gain_comparison'])) {
                $output .= $indent . "   📊 GAIN COMPARISON:\n";
                
                // Sort by gain ratio descending
                $sorted = $treeAnalysis['gain_comparison'];
                uasort($sorted, fn($a, $b) => $b['gain_ratio'] <=> $a['gain_ratio']);
                
                foreach ($sorted as $attribute => $stats) {
                    $selected = $stats['selected'] ? '👑 ' : '   ';
                    $output .= $indent . "   {$selected}{$attribute}: ";
                    $output .= "IG=" . $stats['information_gain'] . ", ";
                    $output .= "GR=" . $stats['gain_ratio'];
                    $output .= $stats['selected'] ? " ← CHOSEN" : "";
                    $output .= "\n";
                }
            }
            
            // Display children
            if (isset($treeAnalysis['children'])) {
                foreach ($treeAnalysis['children'] as $value => $child) {
                    $output .= $indent . "├─ {$treeAnalysis['split_attribute']} = '{$value}':\n";
                    $output .= $this->displayGainComparison($child, $depth + 1);
                }
            }
        }
        
        return $output;
    }

    /**
     * Recursively collect tree statistics
     */
    private function collectTreeStats(array $tree, array &$stats, int $depth): void
    {
        $stats['total_nodes']++;
        $stats['max_depth'] = max($stats['max_depth'], $depth);

        if ($tree['type'] === 'leaf') {
            $stats['leaf_nodes']++;
        } else {
            $stats['internal_nodes']++;
            $stats['attributes_used'][] = $tree['attribute'];
            
            foreach ($tree['children'] as $child) {
                $this->collectTreeStats($child, $stats, $depth + 1);
            }
        }
    }
}