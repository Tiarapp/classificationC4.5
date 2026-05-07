<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dataset extends Model
{
    protected $fillable = [
        'respondent_id',
        'durasi_penggunaan',
        'frekuensi_akses',
        'perhatian_konten',
        'penghayatan',
        'label_intensitas',
        'is_training_data'
    ];

    protected $casts = [
        'perhatian_konten' => 'integer',
        'penghayatan' => 'integer',
        'is_training_data' => 'boolean'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    public function getDurasiOptions(): array
    {
        return ['<=1 jam', '1-3 jam', '3-5 jam', '>5 jam'];
    }

    public function getFrekuensiOptions(): array
    {
        return ['1-2x', '3-5x', '>5x'];
    }

    public function getLabelOptions(): array
    {
        return ['rendah', 'sedang', 'tinggi'];
    }

    /**
     * Get the respondent that owns the dataset.
     */
    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    /**
     * Scope a query to only include training data.
     */
    public function scopeTrainingData($query)
    {
        return $query->where('is_training_data', true);
    }

    /**
     * Scope a query to only include testing data.
     */
    public function scopeTestingData($query)
    {
        return $query->where('is_training_data', false);
    }
}
