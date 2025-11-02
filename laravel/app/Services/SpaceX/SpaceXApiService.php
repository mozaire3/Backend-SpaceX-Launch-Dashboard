<?php

namespace App\Services\SpaceX;

use App\Exceptions\SpaceXApiException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SpaceXApiService
{
    private string $baseUrl;
    private int $timeout;
    private int $cacheTime;

    public function __construct()
    {
        $this->baseUrl = config('spacex.api_base_url', 'https://api.spacexdata.com');
        $this->timeout = config('spacex.timeout', 30);
        $this->cacheTime = config('spacex.cache_time', 3600); // 1 hour default
    }

    /**
     * Get all launches from SpaceX API
     */
    public function getLaunches(array $query = []): array
    {
        $cacheKey = 'spacex_launches_' . md5(serialize($query));
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($query) {
            return $this->makeRequest('/v5/launches', $query);
        });
    }

    /**
     * Get a specific launch by ID
     */
    public function getLaunch(string $id): array
    {
        $cacheKey = "spacex_launch_{$id}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
            return $this->makeRequest("/v5/launches/{$id}");
        });
    }

    /**
     * Get all rockets from SpaceX API
     */
    public function getRockets(): array
    {
        $cacheKey = 'spacex_rockets';
        
        return Cache::remember($cacheKey, $this->cacheTime, function () {
            return $this->makeRequest('/v4/rockets');
        });
    }

    /**
     * Get a specific rocket by ID
     */
    public function getRocket(string $id): array
    {
        $cacheKey = "spacex_rocket_{$id}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
            return $this->makeRequest("/v4/rockets/{$id}");
        });
    }

    /**
     * Get all launchpads from SpaceX API
     */
    public function getLaunchpads(): array
    {
        $cacheKey = 'spacex_launchpads';
        
        return Cache::remember($cacheKey, $this->cacheTime, function () {
            return $this->makeRequest('/v4/launchpads');
        });
    }

    /**
     * Get a specific launchpad by ID
     */
    public function getLaunchpad(string $id): array
    {
        $cacheKey = "spacex_launchpad_{$id}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
            return $this->makeRequest("/v4/launchpads/{$id}");
        });
    }

    /**
     * Make HTTP request to SpaceX API
     */
    private function makeRequest(string $endpoint, array $query = []): array
    {
        try {
            Log::info("SpaceX API Request", [
                'endpoint' => $endpoint,
                'query' => $query
            ]);

            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . $endpoint, $query);

            if (!$response->successful()) {
                throw new SpaceXApiException(
                    "SpaceX API request failed: {$response->status()} - {$response->body()}",
                    $response->status()
                );
            }

            $data = $response->json();

            if ($data === null) {
                throw new SpaceXApiException("Invalid JSON response from SpaceX API");
            }

            Log::info("SpaceX API Response received", [
                'endpoint' => $endpoint,
                'data_count' => is_array($data) ? count($data) : 1
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error("SpaceX API Error", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($e instanceof SpaceXApiException) {
                throw $e;
            }

            throw new SpaceXApiException("Failed to connect to SpaceX API: " . $e->getMessage());
        }
    }

    /**
     * Clear all SpaceX cache
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            'spacex_launches_*',
            'spacex_launch_*',
            'spacex_rockets',
            'spacex_rocket_*',
            'spacex_launchpads',
            'spacex_launchpad_*'
        ];

        foreach ($cacheKeys as $pattern) {
            Cache::forget($pattern);
        }

        Log::info("SpaceX cache cleared");
    }

    /**
     * Check API health
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/v4/company');
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("SpaceX API health check failed", ['error' => $e->getMessage()]);
            return false;
        }
    }
}