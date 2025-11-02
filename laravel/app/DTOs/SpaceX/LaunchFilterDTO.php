<?php

namespace App\DTOs\SpaceX;

class LaunchFilterDTO
{
    public function __construct(
        public readonly ?int $year = null,
        public readonly ?bool $success = null,
        public readonly ?bool $upcoming = null,
        public readonly ?string $rocketSpacexId = null,
        public readonly ?string $launchpadSpacexId = null,
        public readonly ?string $search = null,
        public readonly int $page = 1,
        public readonly int $perPage = 20,
        public readonly string $sortBy = 'date_utc',
        public readonly string $sortDirection = 'desc',
    ) {}

    /**
     * Create DTO from request data
     */
    public static function fromRequest(array $data): self
    {
        $sortBy = $data['sort_by'] ?? 'date_utc';
        $sortDirection = $data['sort_direction'] ?? 'desc';
        
        return new self(
            year: isset($data['year']) ? (int) $data['year'] : null,
            success: isset($data['success']) ? filter_var($data['success'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            upcoming: isset($data['upcoming']) ? filter_var($data['upcoming'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            rocketSpacexId: $data['rocket_id'] ?? null,
            launchpadSpacexId: $data['launchpad_id'] ?? null,
            search: $data['search'] ?? null,
            page: isset($data['page']) ? max(1, (int) $data['page']) : 1,
            perPage: isset($data['per_page']) ? min(100, max(1, (int) $data['per_page'])) : 20,
            sortBy: in_array($sortBy, ['date_utc', 'name', 'flight_number']) ? $sortBy : 'date_utc',
            sortDirection: in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc',
        );
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'year' => $this->year,
            'success' => $this->success,
            'upcoming' => $this->upcoming,
            'rocket_id' => $this->rocketSpacexId,
            'launchpad_id' => $this->launchpadSpacexId,
            'search' => $this->search,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
        ];
    }

    /**
     * Check if any filters are applied
     */
    public function hasFilters(): bool
    {
        return $this->year !== null ||
               $this->success !== null ||
               $this->upcoming !== null ||
               $this->rocketSpacexId !== null ||
               $this->launchpadSpacexId !== null ||
               $this->search !== null;
    }
}