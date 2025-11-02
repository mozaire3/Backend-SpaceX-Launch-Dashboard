<?php

namespace App\DTOs\SpaceX;

use Carbon\Carbon;

class LaunchDTO
{
    public function __construct(
        public readonly string $spacexId,
        public readonly ?string $flightNumber,
        public readonly string $name,
        public readonly ?Carbon $dateUtc,
        public readonly ?Carbon $dateLocal,
        public readonly ?bool $success,
        public readonly array $failures,
        public readonly bool $upcoming,
        public readonly ?string $details,
        public readonly ?string $rocketSpacexId,
        public readonly ?string $rocketName,
        public readonly ?string $launchpadSpacexId,
        public readonly ?string $launchpadName,
        public readonly array $links,
        public readonly array $payloads,
        public readonly array $crew,
        public readonly array $cores,
    ) {}

    /**
     * Create DTO from SpaceX API response
     */
    public static function fromApiResponse(array $data, ?string $rocketName = null, ?string $launchpadName = null): self
    {
        return new self(
            spacexId: $data['id'],
            flightNumber: $data['flight_number'] ?? null,
            name: $data['name'],
            dateUtc: isset($data['date_utc']) ? Carbon::parse($data['date_utc']) : null,
            dateLocal: isset($data['date_local']) ? Carbon::parse($data['date_local']) : null,
            success: $data['success'] ?? null,
            failures: $data['failures'] ?? [],
            upcoming: $data['upcoming'] ?? false,
            details: $data['details'] ?? null,
            rocketSpacexId: $data['rocket'] ?? null,
            rocketName: $rocketName,
            launchpadSpacexId: $data['launchpad'] ?? null,
            launchpadName: $launchpadName,
            links: $data['links'] ?? [],
            payloads: $data['payloads'] ?? [],
            crew: $data['crew'] ?? [],
            cores: $data['cores'] ?? [],
        );
    }

    /**
     * Convert to array for database storage
     */
    public function toArray(): array
    {
        return [
            'spacex_id' => $this->spacexId,
            'flight_number' => $this->flightNumber,
            'name' => $this->name,
            'date_utc' => $this->dateUtc?->toDateTimeString(),
            'date_local' => $this->dateLocal?->toDateTimeString(),
            'success' => $this->success,
            'failures' => $this->failures,
            'upcoming' => $this->upcoming,
            'details' => $this->details,
            'rocket_spacex_id' => $this->rocketSpacexId,
            'rocket_name' => $this->rocketName,
            'launchpad_spacex_id' => $this->launchpadSpacexId,
            'launchpad_name' => $this->launchpadName,
            'links' => $this->links,
            'payloads' => $this->payloads,
            'crew' => $this->crew,
            'cores' => $this->cores,
        ];
    }

    /**
     * Get launch status
     */
    public function getStatus(): string
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
    public function getDaysUntilLaunch(): ?int
    {
        if (!$this->upcoming || !$this->dateUtc) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->dateUtc, false);
    }
}