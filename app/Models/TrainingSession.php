<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    protected $fillable = [
        'algorithm',
        'parameters',
        'train_data_count',
        'test_data_count', 
        'accuracy',
        'training_time',
        'model_data'
    ];

    protected $casts = [
        'train_data_count' => 'integer',
        'test_data_count' => 'integer',
        'accuracy' => 'decimal:4',
        'training_time' => 'decimal:4',
        'parameters' => 'array',
        'model_data' => 'array'
    ];

    /**
     * Get the predictions for the training session.
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    /**
     * Scope a query to only include completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('accuracy', '>', 0);
    }

    /**
     * Get formatted accuracy percentage
     */
    public function getAccuracyPercentageAttribute(): string
    {
        return number_format($this->accuracy * 100, 2) . '%';
    }

    /**
     * Get formatted training time
     */
    public function getFormattedTrainingTimeAttribute(): string
    {
        return number_format($this->training_time, 2) . 's';
    }

    /**
     * Get the latest trained model
     */
    public static function getLatestModel(): ?TrainingSession
    {
        return self::completed()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the best model by accuracy
     */
    public static function getBestModel(): ?TrainingSession
    {
        return self::completed()
            ->orderBy('accuracy', 'desc')
            ->first();
    }
}