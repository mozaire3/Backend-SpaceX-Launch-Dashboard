<?php

namespace App\DTOs\SpaceX;

class LaunchpadDTO
{
    public function __construct(
        public readonly string $spacexId,
        public readonly string $name,
        public readonly ?string $fullName,
        public readonly ?string $locality,
        public readonly ?string $region,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly int $launchAttempts,
        public readonly int $launchSuccesses,
        public readonly ?string $status,
        public readonly ?string $details,
        public readonly array $images,
    ) {}

    /**
     * Create DTO from SpaceX API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            spacexId: $data['id'],
            name: $data['name'],
            fullName: $data['full_name'] ?? null,
            locality: $data['locality'] ?? null,
            region: $data['region'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            launchAttempts: $data['launch_attempts'] ?? 0,
            launchSuccesses: $data['launch_successes'] ?? 0,
            status: $data['status'] ?? null,
            details: $data['details'] ?? null,
            images: $data['images']['large'] ?? [],
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
            'full_name' => $this->fullName,
            'locality' => $this->locality,
            'region' => $this->region,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'launch_attempts' => $this->launchAttempts,
            'launch_successes' => $this->launchSuccesses,
            'status' => $this->status,
            'details' => $this->details,
            'images' => $this->images,
        ];
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRate(): float
    {
        if ($this->launchAttempts === 0) {
            return 0;
        }
        
        return round(($this->launchSuccesses / $this->launchAttempts) * 100, 2);
    }
}