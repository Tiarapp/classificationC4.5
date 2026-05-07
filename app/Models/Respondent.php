<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Respondent extends Model
{
    protected $fillable = [
        'nama',
        'nim', 
        'jurusan',
        'semester'
    ];

    protected $casts = [
        'semester' => 'integer'
    ];

    /**
     * Get the datasets for the respondent.
     */
    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class);
    }
}
