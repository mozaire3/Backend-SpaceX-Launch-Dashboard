<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rocket extends Model
{
    use HasFactory;

    protected $fillable = [
        'spacex_id',
        'name',
        'type',
        'active',
        'stages',
        'boosters',
        'cost_per_launch',
        'success_rate_pct',
        'first_flight',
        'country',
        'company',
        'wikipedia',
        'description',
        'flickr_images',
    ];

    protected $casts = [
        'active' => 'boolean',
        'cost_per_launch' => 'decimal:2',
        'success_rate_pct' => 'decimal:2',
        'first_flight' => 'date',
        'flickr_images' => 'array',
    ];

    /**
     * Get the launches for this rocket
     */
    public function launches(): HasMany
    {
        return $this->hasMany(Launch::class, 'rocket_spacex_id', 'spacex_id');
    }

    /**
     * Get successful launches count
     */
    public function getSuccessfulLaunchesCountAttribute(): int
    {
        return $this->launches()->where('success', true)->count();
    }

    /**
     * Get total launches count
     */
    public function getTotalLaunchesCountAttribute(): int
    {
        return $this->launches()->count();
    }
}
