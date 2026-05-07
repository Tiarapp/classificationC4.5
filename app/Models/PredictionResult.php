<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PredictionResult extends Model
{
    protected $fillable = [
        'nama',
        'durasi_penggunaan',
        'frekuensi_akses',
        'perhatian_konten',
        'penghayatan',
        'predicted_label',
        'prediction_details',
        'confidence_score',
        'training_session_id',
        'model_type',
        'model_accuracy',
        'notes',
        'user_agent',
        'ip_address'
    ];

    protected $casts = [
        'prediction_details' => 'array',
        'confidence_score' => 'decimal:4',
        'model_accuracy' => 'decimal:2',
        'perhatian_konten' => 'integer',
        'penghayatan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship dengan TrainingSession
     */
    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    /**
     * Scope untuk filter berdasarkan label prediksi
     */
    public function scopeByPredictedLabel($query, $label)
    {
        return $query->where('predicted_label', $label);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk prediksi terbaru
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Accessor untuk format tanggal yang user-friendly
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M Y H:i');
    }

    /**
     * Accessor untuk confidence percentage
     */
    public function getConfidencePercentageAttribute()
    {
        return $this->confidence_score ? round($this->confidence_score * 100, 2) : null;
    }

    /**
     * Accessor untuk input summary
     */
    public function getInputSummaryAttribute()
    {
        return [
            'Nama' => $this->nama ?? 'Anonim',
            'Durasi' => $this->durasi_penggunaan,
            'Frekuensi' => $this->frekuensi_akses,
            'Perhatian Konten' => $this->perhatian_konten,
            'Penghayatan' => $this->penghayatan
        ];
    }

    /**
     * Method untuk export ke array
     */
    public function toExportArray()
    {
        return [
            'ID' => $this->id,
            'Tanggal' => $this->formatted_date,
            'Nama' => $this->nama ?? 'Anonim',
            'Durasi Penggunaan' => $this->durasi_penggunaan,
            'Frekuensi Akses' => $this->frekuensi_akses,
            'Perhatian Konten' => $this->perhatian_konten,
            'Penghayatan' => $this->penghayatan,
            'Prediksi' => $this->predicted_label,
            'Confidence (%)' => $this->confidence_percentage,
            'Model' => $this->model_type,
            'Akurasi Model (%)' => $this->model_accuracy,
            'Catatan' => $this->notes
        ];
    }

    /**
     * Static method untuk statistik
     */
    public static function getStats($dateRange = null)
    {
        $query = self::query();
        
        if ($dateRange) {
            $query->byDateRange($dateRange['start'], $dateRange['end']);
        }

        $total = $query->count();
        
        return [
            'total' => $total,
            'by_label' => [
                'RENDAH' => $query->clone()->byPredictedLabel('RENDAH')->count(),
                'SEDANG' => $query->clone()->byPredictedLabel('SEDANG')->count(),
                'TINGGI' => $query->clone()->byPredictedLabel('TINGGI')->count()
            ],
            'avg_confidence' => $query->clone()->avg('confidence_score'),
            'latest' => $query->clone()->recent(1)->first()
        ];
    }
}
