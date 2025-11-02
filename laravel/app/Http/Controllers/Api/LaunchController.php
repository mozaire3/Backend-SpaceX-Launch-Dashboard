<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpaceX\LaunchService;
use App\DTOs\SpaceX\LaunchFilterDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LaunchController extends Controller
{
    public function __construct(
        private LaunchService $launchService
    ) {}

        /**
     * Liste paginée des lancements
     * 
     * Récupère les lancements avec filtres et pagination.
     * 
     * @authenticated
     * 
     * @queryParam page integer Numéro de la page (défaut: 1). Example: 1
     * @queryParam per_page integer Nombre d'éléments par page (défaut: 15, max: 100). Example: 10
     * @queryParam year integer Filtrer par année. Example: 2024
     * @queryParam success boolean Filtrer par succès (true) ou échec (false). Example: true
     * @queryParam rocket string Filtrer par nom de fusée. Example: Falcon 9
     * @queryParam search string Recherche dans le nom du lancement. Example: Starlink
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "data": [
     *       {
     *         "spacex_id": "64f7a123abc",
     *         "name": "Starlink Group 6-1",
     *         "rocket_name": "Falcon 9",
     *         "launchpad_name": "Kennedy Space Center",
     *         "date_utc": "2024-01-15T10:30:00.000Z",
     *         "success": true,
     *         "details": "Mission successful"
     *       }
     *     ],
     *     "current_page": 1,
     *     "total": 100,
     *     "per_page": 10,
     *     "last_page": 10
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Create filter DTO from request
            $filters = LaunchFilterDTO::fromRequest($request->all());

            // Get launches
            $launches = $this->launchService->getLaunches($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'launches' => $launches->items(),
                    'pagination' => [
                        'current_page' => $launches->currentPage(),
                        'last_page' => $launches->lastPage(),
                        'per_page' => $launches->perPage(),
                        'total' => $launches->total(),
                        'from' => $launches->firstItem(),
                        'to' => $launches->lastItem(),
                    ],
                    'filters' => $filters->toArray(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching launches',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get a specific launch by SpaceX ID
     */
    public function show(string $spacexId): JsonResponse
    {
        try {
            $launch = $this->launchService->getLaunchBySpacexId($spacexId);

            if (!$launch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Launch not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $launch,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching launch details',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get next upcoming launch
     */
    public function upcoming(): JsonResponse
    {
        try {
            $nextLaunch = $this->launchService->getNextUpcomingLaunch();

            return response()->json([
                'success' => true,
                'data' => $nextLaunch,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching upcoming launch',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get launches statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->launchService->getLaunchesStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching launch statistics',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get launches by year for charts
     */
    public function byYear(): JsonResponse
    {
        try {
            $launchesByYear = $this->launchService->getLaunchesByYear();

            return response()->json([
                'success' => true,
                'data' => $launchesByYear,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching launches by year',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get available years for filtering
     */
    public function availableYears(): JsonResponse
    {
        try {
            $years = $this->launchService->getAvailableYears();

            return response()->json([
                'success' => true,
                'data' => $years,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching available years',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
