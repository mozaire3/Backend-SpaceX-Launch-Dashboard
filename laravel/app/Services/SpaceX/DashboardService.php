<?php

namespace App\Services\SpaceX;

use App\Models\Launch;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private LaunchService $launchService
    ) {}

    /**
     * Get complete dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'kpis' => $this->getKPIs(),
            'charts' => $this->getChartsData(),
            'next_launch' => $this->getNextLaunch(),
            'recent_launches' => $this->getRecentLaunches(),
        ];
    }

    /**
     * Get Key Performance Indicators
     */
    public function getKPIs(): array
    {
        $stats = $this->launchService->getLaunchesStats();
        $nextLaunch = $this->launchService->getNextUpcomingLaunch();

        return [
            'total_launches' => $stats['total_launches'],
            'success_rate' => $stats['success_rate'],
            'next_launch' => $nextLaunch ? [
                'name' => $nextLaunch->name,
                'date' => $nextLaunch->date_utc?->format('Y-m-d H:i:s'),
                'days_until' => $nextLaunch->days_until_launch,
                'rocket' => $nextLaunch->rocket_name,
                'launchpad' => $nextLaunch->launchpad_name,
            ] : null,
            'upcoming_launches' => $stats['upcoming_launches'],
        ];
    }

    /**
     * Get data for charts
     */
    public function getChartsData(): array
    {
        return [
            'launches_by_year' => $this->getLaunchesByYearChart(),
            'success_rate_by_year' => $this->getSuccessRateByYearChart(),
            'launches_by_rocket' => $this->getLaunchesByRocketChart(),
            'launches_by_launchpad' => $this->getLaunchesByLaunchpadChart(),
        ];
    }

    /**
     * Get launches by year for bar chart
     */
    private function getLaunchesByYearChart(): array
    {
        $launchesByYear = $this->launchService->getLaunchesByYear();
        
        return [
            'labels' => array_column($launchesByYear, 'year'),
            'datasets' => [
                [
                    'label' => 'Successful',
                    'data' => array_column($launchesByYear, 'successful'),
                    'backgroundColor' => '#10B981', // Green
                ],
                [
                    'label' => 'Failed',
                    'data' => array_column($launchesByYear, 'failed'),
                    'backgroundColor' => '#EF4444', // Red
                ],
            ],
        ];
    }

    /**
     * Get success rate by year for line chart
     */
    private function getSuccessRateByYearChart(): array
    {
        $launchesByYear = $this->launchService->getLaunchesByYear();
        
        return [
            'labels' => array_column($launchesByYear, 'year'),
            'datasets' => [
                [
                    'label' => 'Success Rate (%)',
                    'data' => array_column($launchesByYear, 'success_rate'),
                    'borderColor' => '#3B82F6', // Blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
        ];
    }

    /**
     * Get launches count by rocket
     */
    private function getLaunchesByRocketChart(): array
    {
        $data = Launch::selectRaw('rocket_name, COUNT(*) as count')
            ->whereNotNull('rocket_name')
            ->groupBy('rocket_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'rocket_name')
            ->toArray();

        return [
            'labels' => array_keys($data),
            'data' => array_values($data),
        ];
    }

    /**
     * Get launches count by launchpad
     */
    private function getLaunchesByLaunchpadChart(): array
    {
        $data = Launch::selectRaw('launchpad_name, COUNT(*) as count')
            ->whereNotNull('launchpad_name')
            ->groupBy('launchpad_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'launchpad_name')
            ->toArray();

        return [
            'labels' => array_keys($data),
            'data' => array_values($data),
        ];
    }

    /**
     * Get next launch information
     */
    private function getNextLaunch(): ?array
    {
        $nextLaunch = $this->launchService->getNextUpcomingLaunch();
        
        if (!$nextLaunch) {
            return null;
        }

        return [
            'id' => $nextLaunch->spacex_id,
            'name' => $nextLaunch->name,
            'date_utc' => $nextLaunch->date_utc?->toISOString(),
            'date_local' => $nextLaunch->date_local?->toISOString(),
            'days_until' => $nextLaunch->days_until_launch,
            'rocket' => [
                'id' => $nextLaunch->rocket_spacex_id,
                'name' => $nextLaunch->rocket_name,
            ],
            'launchpad' => [
                'id' => $nextLaunch->launchpad_spacex_id,
                'name' => $nextLaunch->launchpad_name,
            ],
            'details' => $nextLaunch->details,
            'links' => $nextLaunch->links,
        ];
    }

    /**
     * Get recent launches
     */
    private function getRecentLaunches(int $limit = 10): array
    {
        return Launch::completed()
            ->orderBy('date_utc', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($launch) {
                return [
                    'id' => $launch->spacex_id,
                    'name' => $launch->name,
                    'date_utc' => $launch->date_utc?->toISOString(),
                    'success' => $launch->success,
                    'rocket' => $launch->rocket_name,
                    'launchpad' => $launch->launchpad_name,
                ];
            })
            ->toArray();
    }
}