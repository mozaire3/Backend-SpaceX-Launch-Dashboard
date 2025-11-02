<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SpaceX\DashboardService;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private AuthService $authService
    ) {}

        /**
     * Vue d'ensemble du tableau de bord
     * 
     * Retourne les KPIs principaux et les lancements récents.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "kpis": {
     *       "total_launches": 250,
     *       "successful_launches": 230,
     *       "failed_launches": 20,
     *       "success_rate": 92.0,
     *       "upcoming_launches": 15
     *     },
     *     "recent_launches": [
     *       {
     *         "spacex_id": "64f7a123abc",
     *         "name": "Starlink Group 6-1",
     *         "rocket_name": "Falcon 9",
     *         "date_utc": "2024-01-15T10:30:00.000Z",
     *         "success": true
     *       }
     *     ]
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $dashboardData = $this->dashboardService->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get KPIs only
     */
    public function kpis(): JsonResponse
    {
        try {
            $kpis = $this->dashboardService->getKPIs();

            return response()->json([
                'success' => true,
                'data' => $kpis,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching KPIs',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get charts data only
     */
    public function charts(): JsonResponse
    {
        try {
            $chartsData = $this->dashboardService->getChartsData();

            return response()->json([
                'success' => true,
                'data' => $chartsData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching charts data',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(): JsonResponse
    {
        try {
            $profile = $this->authService->getProfile();

            return response()->json([
                'success' => true,
                'data' => $profile,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching profile',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
