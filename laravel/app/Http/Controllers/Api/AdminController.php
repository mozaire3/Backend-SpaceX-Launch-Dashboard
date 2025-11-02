<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpaceX\LaunchService;
use App\Services\SpaceX\SpaceXApiService;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct(
        private LaunchService $launchService,
        private SpaceXApiService $spaceXApiService,
        private AuthService $authService
    ) {}

    /**
     * Synchronisation des données SpaceX (Admin uniquement)
     * 
     * Récupère et synchronise toutes les données de l'API SpaceX v5.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Data synchronized successfully",
     *   "data": {
     *     "total_from_api": 200,
     *     "synced": 15,
     *     "updated": 185
     *   }
     * }
     * 
     * @response 403 {
     *   "success": false,
     *   "message": "Access denied. Admin role required."
     * }
     */
    public function syncData(): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$this->authService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.',
                ], 403);
            }

            // Log::info('Admin sync started by user', ['user_id' => auth()->id()]);

            // Sync launches
            $syncResults = $this->launchService->syncFromApi();

            // Log::info('Admin sync completed', $syncResults);

            return response()->json([
                'success' => true,
                'message' => 'Data synchronized successfully',
                'data' => $syncResults,
            ]);

        } catch (\Exception $e) {
            Log::error('Admin sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during synchronization',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Clear SpaceX API cache (Admin only)
     */
    public function clearCache(): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$this->authService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.',
                ], 403);
            }

            $this->spaceXApiService->clearCache();

            // Log::info('Cache cleared by admin', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clearing cache',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check SpaceX API health (Admin only)
     */
    public function apiHealth(): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$this->authService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.',
                ], 403);
            }

            $isHealthy = $this->spaceXApiService->healthCheck();

            return response()->json([
                'success' => true,
                'data' => [
                    'api_healthy' => $isHealthy,
                    'checked_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during health check',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get admin dashboard stats
     */
    public function stats(): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$this->authService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.',
                ], 403);
            }

            $stats = $this->launchService->getLaunchesStats();
            $isApiHealthy = $this->spaceXApiService->healthCheck();

            return response()->json([
                'success' => true,
                'data' => [
                    'launches' => $stats,
                    'api_status' => [
                        'healthy' => $isApiHealthy,
                        'checked_at' => now()->toISOString(),
                    ],
                    'system' => [
                        'last_sync' => null, // You can implement this later
                        'cache_enabled' => config('cache.default') !== 'array',
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching admin stats',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
