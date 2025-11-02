<?php

namespace App\Services\SpaceX;

use App\Models\Rocket;
use App\DTOs\SpaceX\RocketDTO;
use Illuminate\Database\Eloquent\Collection;

class RocketService
{
    public function __construct(
        private SpaceXApiService $spaceXApiService
    ) {}

    /**
     * Get all rockets
     */
    public function getAllRockets(): Collection
    {
        return Rocket::orderBy('name')->get();
    }

    /**
     * Get a specific rocket by SpaceX ID
     */
    public function getRocketBySpacexId(string $spacexId): ?Rocket
    {
        return Rocket::where('spacex_id', $spacexId)->first();
    }

    /**
     * Get active rockets
     */
    public function getActiveRockets(): Collection
    {
        return Rocket::where('active', true)->orderBy('name')->get();
    }

    /**
     * Sync rockets from SpaceX API
     */
    public function syncFromApi(): array
    {
        $apiRockets = $this->spaceXApiService->getRockets();
        $synced = 0;
        $updated = 0;

        foreach ($apiRockets as $apiRocket) {
            $rocketDTO = RocketDTO::fromApiResponse($apiRocket);
            
            $rocket = Rocket::updateOrCreate(
                ['spacex_id' => $rocketDTO->spacexId],
                $rocketDTO->toArray()
            );

            if ($rocket->wasRecentlyCreated) {
                $synced++;
            } else {
                $updated++;
            }
        }

        return [
            'total_from_api' => count($apiRockets),
            'synced' => $synced,
            'updated' => $updated,
        ];
    }
}