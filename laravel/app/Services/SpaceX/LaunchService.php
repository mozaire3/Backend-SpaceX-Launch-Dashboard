<?php

namespace App\Services\SpaceX;

use App\Models\Launch;
use App\DTOs\SpaceX\LaunchDTO;
use App\DTOs\SpaceX\LaunchFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaunchService
{
    public function __construct(
        private SpaceXApiService $spaceXApiService
    ) {}

    /**
     * Get paginated launches with filters
     */
    public function getLaunches(LaunchFilterDTO $filters): LengthAwarePaginator
    {
        $query = Launch::query();

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply search
        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters->search}%")
                  ->orWhere('details', 'like', "%{$filters->search}%")
                  ->orWhere('flight_number', 'like', "%{$filters->search}%");
            });
        }

        // Apply sorting
        $query->orderBy($filters->sortBy, $filters->sortDirection);

        return $query->paginate($filters->perPage, ['*'], 'page', $filters->page);
    }

    /**
     * Get a specific launch by SpaceX ID
     */
    public function getLaunchBySpacexId(string $spacexId): ?Launch
    {
        return Launch::where('spacex_id', $spacexId)->first();
    }

    /**
     * Get next upcoming launch
     */
    public function getNextUpcomingLaunch(): ?Launch
    {
        // Rechercher d'abord les lancements marqués comme upcoming ET dans le futur
        $upcomingLaunch = Launch::upcoming()
            ->where('date_utc', '>', Carbon::now())
            ->orderBy('date_utc')
            ->first();
            
        // Si aucun, prendre le prochain lancement dans le futur (peu importe le flag upcoming)
        if (!$upcomingLaunch) {
            $upcomingLaunch = Launch::where('date_utc', '>', Carbon::now())
                ->orderBy('date_utc')
                ->first();
        }
        
        return $upcomingLaunch;
    }

    /**
     * Get launches statistics
     */
    public function getLaunchesStats(): array
    {
        $totalLaunches = Launch::completed()->count();
        $successfulLaunches = Launch::successful()->count();
        $failedLaunches = Launch::failed()->count();
        // Compter seulement les vrais lancements à venir (dans le futur)
        $upcomingLaunches = Launch::where('date_utc', '>', Carbon::now())->count();

        $successRate = $totalLaunches > 0 ? round(($successfulLaunches / $totalLaunches) * 100, 2) : 0;

        return [
            'total_launches' => $totalLaunches,
            'successful_launches' => $successfulLaunches,
            'failed_launches' => $failedLaunches,
            'upcoming_launches' => $upcomingLaunches,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Get launches by year statistics
     */
    public function getLaunchesByYear(): array
    {
        return Launch::completed()
            ->selectRaw('YEAR(date_utc) as year, COUNT(*) as total, SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful')
            ->groupByRaw('YEAR(date_utc)')
            ->orderBy('year')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => (int) $item->year,
                    'total' => $item->total,
                    'successful' => $item->successful,
                    'failed' => $item->total - $item->successful,
                    'success_rate' => $item->total > 0 ? round(($item->successful / $item->total) * 100, 2) : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Sync launches from SpaceX API
     */
    public function syncFromApi(): array
    {
        $apiLaunches = $this->spaceXApiService->getLaunches();
        $synced = 0;
        $updated = 0;

        // Cache pour les rockets et launchpads
        $rocketsCache = [];
        $launchpadsCache = [];

        foreach ($apiLaunches as $apiLaunch) {
            // Récupérer les noms des rockets et launchpads si nécessaire
            $rocketName = null;
            $launchpadName = null;

            if ($apiLaunch['rocket'] && !isset($rocketsCache[$apiLaunch['rocket']])) {
                try {
                    $rocket = $this->spaceXApiService->getRocket($apiLaunch['rocket']);
                    $rocketsCache[$apiLaunch['rocket']] = $rocket['name'] ?? null;
                } catch (\Exception $e) {
                    $rocketsCache[$apiLaunch['rocket']] = null;
                }
            }
            $rocketName = $rocketsCache[$apiLaunch['rocket']] ?? null;

            if ($apiLaunch['launchpad'] && !isset($launchpadsCache[$apiLaunch['launchpad']])) {
                try {
                    $launchpad = $this->spaceXApiService->getLaunchpad($apiLaunch['launchpad']);
                    $launchpadsCache[$apiLaunch['launchpad']] = $launchpad['name'] ?? null;
                } catch (\Exception $e) {
                    $launchpadsCache[$apiLaunch['launchpad']] = null;
                }
            }
            $launchpadName = $launchpadsCache[$apiLaunch['launchpad']] ?? null;

            $launchDTO = LaunchDTO::fromApiResponse($apiLaunch, $rocketName, $launchpadName);
            
            $launch = Launch::updateOrCreate(
                ['spacex_id' => $launchDTO->spacexId],
                $launchDTO->toArray()
            );

            if ($launch->wasRecentlyCreated) {
                $synced++;
            } else {
                $updated++;
            }
        }

        return [
            'total_from_api' => count($apiLaunches),
            'synced' => $synced,
            'updated' => $updated,
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, LaunchFilterDTO $filters): void
    {
        if ($filters->year !== null) {
            $query->byYear($filters->year);
        }

        if ($filters->success !== null) {
            if ($filters->success) {
                $query->successful();
            } else {
                $query->failed();
            }
        }

        if ($filters->upcoming !== null) {
            if ($filters->upcoming) {
                $query->upcoming();
            } else {
                $query->completed();
            }
        }

        if ($filters->rocketSpacexId !== null) {
            $query->where('rocket_spacex_id', $filters->rocketSpacexId);
        }

        if ($filters->launchpadSpacexId !== null) {
            $query->where('launchpad_spacex_id', $filters->launchpadSpacexId);
        }
    }

    /**
     * Get available years from launches
     */
    public function getAvailableYears(): array
    {
        $years = Launch::select(DB::raw('YEAR(date_utc) as year'))
            ->whereNotNull('date_utc')
            ->groupBy(DB::raw('YEAR(date_utc)'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return $years;
    }
}