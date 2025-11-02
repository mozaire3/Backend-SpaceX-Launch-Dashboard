<?php

namespace App\Services\SpaceX;

use App\Models\Launchpad;
use App\DTOs\SpaceX\LaunchpadDTO;
use Illuminate\Database\Eloquent\Collection;

class LaunchpadService
{
    public function __construct(
        private SpaceXApiService $spaceXApiService
    ) {}

    /**
     * Get all launchpads
     */
    public function getAllLaunchpads(): Collection
    {
        return Launchpad::orderBy('name')->get();
    }

    /**
     * Get a specific launchpad by SpaceX ID
     */
    public function getLaunchpadBySpacexId(string $spacexId): ?Launchpad
    {
        return Launchpad::where('spacex_id', $spacexId)->first();
    }

    /**
     * Get active launchpads
     */
    public function getActiveLaunchpads(): Collection
    {
        return Launchpad::where('status', 'active')->orderBy('name')->get();
    }

    /**
     * Sync launchpads from SpaceX API
     */
    public function syncFromApi(): array
    {
        $apiLaunchpads = $this->spaceXApiService->getLaunchpads();
        $synced = 0;
        $updated = 0;

        foreach ($apiLaunchpads as $apiLaunchpad) {
            $launchpadDTO = LaunchpadDTO::fromApiResponse($apiLaunchpad);
            
            $launchpad = Launchpad::updateOrCreate(
                ['spacex_id' => $launchpadDTO->spacexId],
                $launchpadDTO->toArray()
            );

            if ($launchpad->wasRecentlyCreated) {
                $synced++;
            } else {
                $updated++;
            }
        }

        return [
            'total_from_api' => count($apiLaunchpads),
            'synced' => $synced,
            'updated' => $updated,
        ];
    }
}