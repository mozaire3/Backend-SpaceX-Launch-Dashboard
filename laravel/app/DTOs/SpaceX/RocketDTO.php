<?php

namespace App\DTOs\SpaceX;

use Carbon\Carbon;

class RocketDTO
{
    public function __construct(
        public readonly string $spacexId,
        public readonly string $name,
        public readonly ?string $type,
        public readonly bool $active,
        public readonly ?int $stages,
        public readonly ?int $boosters,
        public readonly ?float $costPerLaunch,
        public readonly ?float $successRatePct,
        public readonly ?Carbon $firstFlight,
        public readonly ?string $country,
        public readonly ?string $company,
        public readonly ?string $wikipedia,
        public readonly ?string $description,
        public readonly array $flickrImages,
    ) {}

    /**
     * Create DTO from SpaceX API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            spacexId: $data['id'],
            name: $data['name'],
            type: $data['type'] ?? null,
            active: $data['active'] ?? true,
            stages: $data['stages'] ?? null,
            boosters: $data['boosters'] ?? null,
            costPerLaunch: $data['cost_per_launch'] ?? null,
            successRatePct: $data['success_rate_pct'] ?? null,
            firstFlight: isset($data['first_flight']) ? Carbon::parse($data['first_flight']) : null,
            country: $data['country'] ?? null,
            company: $data['company'] ?? null,
            wikipedia: $data['wikipedia'] ?? null,
            description: $data['description'] ?? null,
            flickrImages: $data['flickr_images'] ?? [],
        );
    }

    /**
     * Convert to array for database storage
     */
    public function toArray(): array
    {
        return [
            'spacex_id' => $this->spacexId,
            'name' => $this->name,
            'type' => $this->type,
            'active' => $this->active,
            'stages' => $this->stages,
            'boosters' => $this->boosters,
            'cost_per_launch' => $this->costPerLaunch,
            'success_rate_pct' => $this->successRatePct,
            'first_flight' => $this->firstFlight?->toDateString(),
            'country' => $this->country,
            'company' => $this->company,
            'wikipedia' => $this->wikipedia,
            'description' => $this->description,
            'flickr_images' => $this->flickrImages,
        ];
    }
}