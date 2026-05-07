<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    protected $fillable = [
        'training_session_id',
        'durasi_penggunaan',
        'frekuensi_akses',
        'perhatian_konten',
        'penghayatan',
        'predicted_label',
        'confidence_score',
        'decision_path',
        'predicted_by'
    ];

    protected $casts = [
        'perhatian_konten' => 'integer',
        'penghayatan' => 'integer',
        'confidence_score' => 'decimal:4',
        'decision_path' => 'array'
    ];

    /**
     * Get the training session that owns the prediction.
     */
    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    /**
     * Get confidence score as percentage.
     */
    public function getConfidencePercentageAttribute(): string
    {
        return $this->confidence_score 
            ? number_format($this->confidence_score * 100, 2) . '%' 
            : 'N/A';
    }

    /**
     * Get formatted input attributes.
     */
    public function getInputAttributesAttribute(): array
    {
        return [
            'Durasi Penggunaan' => $this->durasi_penggunaan,
            'Frekuensi Akses' => $this->frekuensi_akses,
            'Perhatian Konten' => $this->perhatian_konten,
            'Penghayatan' => $this->penghayatan
        ];
    }

    /**
     * Scope to get recent predictions.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
