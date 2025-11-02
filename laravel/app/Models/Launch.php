<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Launch extends Model
{
    use HasFactory;

    protected $fillable = [
        'spacex_id',
        'flight_number',
        'name',
        'date_utc',
        'date_local',
        'success',
        'failures',
        'upcoming',
        'details',
        'rocket_spacex_id',
        'rocket_name',
        'launchpad_spacex_id',
        'launchpad_name',
        'links',
        'payloads',
        'crew',
        'cores',
    ];

    protected $casts = [
        'date_utc' => 'datetime',
        'date_local' => 'datetime',
        'success' => 'boolean',
        'upcoming' => 'boolean',
        'failures' => 'array',
        'links' => 'array',
        'payloads' => 'array',
        'crew' => 'array',
        'cores' => 'array',
    ];

    /**
     * Get rocket name (stored directly in the model)
     */
    public function getRocketAttribute(): ?string
    {
        return $this->rocket_name;
    }

    /**
     * Get launchpad name (stored directly in the model)
     */
    public function getLaunchpadAttribute(): ?string
    {
        return $this->launchpad_name;
    }

    /**
     * Scope for successful launches
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed launches
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('success', false);
    }

    /**
     * Scope for upcoming launches
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('upcoming', true);
    }

    /**
     * Scope for completed launches
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('upcoming', false);
    }

    /**
     * Scope for launches by year
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->whereRaw('YEAR(date_utc) = ?', [$year]);
    }

    /**
     * Get the launch status as string
     */
    public function getStatusAttribute(): string
    {
        if ($this->upcoming) {
            return 'upcoming';
        }
        
        if ($this->success === null) {
            return 'unknown';
        }
        
        return $this->success ? 'success' : 'failure';
    }

    /**
     * Get days until launch (for upcoming launches)
     */
    public function getDaysUntilLaunchAttribute(): ?int
    {
        if (!$this->upcoming || !$this->date_utc) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->date_utc, false);
    }

    /**
     * Get YouTube video ID from links
     */
    public function getYoutubeIdAttribute(): ?string
    {
        return $this->links['webcast'] ?? null;
    }

    /**
     * Get article link from links
     */
    public function getArticleLinkAttribute(): ?string
    {
        return $this->links['article'] ?? null;
    }

    /**
     * Get Wikipedia link from links
     */
    public function getWikipediaLinkAttribute(): ?string
    {
        return $this->links['wikipedia'] ?? null;
    }
}
