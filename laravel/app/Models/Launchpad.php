<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Launchpad extends Model
{
    use HasFactory;

    protected $fillable = [
        'spacex_id',
        'name',
        'full_name',
        'locality',
        'region',
        'latitude',
        'longitude',
        'launch_attempts',
        'launch_successes',
        'status',
        'details',
        'images',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'images' => 'array',
    ];

    /**
     * Get the launches for this launchpad
     */
    public function launches(): HasMany
    {
        return $this->hasMany(Launch::class, 'launchpad_spacex_id', 'spacex_id');
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->launch_attempts === 0) {
            return 0;
        }
        
        return round(($this->launch_successes / $this->launch_attempts) * 100, 2);
    }
}
