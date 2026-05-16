<?php

namespace App\Filters\Public;

use App\Filters\BaseFilter;

class StoreFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
        'type',
        'nearby',
        'sort',
    ];

    protected array $sortable = [
        'name',
        'created_at',
    ];

    /**
     * Search by store name
     */
    public function search($value): void
    {
        $this->builder->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Filter by store type ID
     */
    public function type($value): void
    {
        $this->builder->where('store_type_id', $value);
    }

    /**
     * Filter by proximity to coordinates
     */
    public function nearby($value): void
    {
        if (!is_array($value) || !isset($value['lat'], $value['lng'])) {
            return;
        }

        $lat = (float) $value['lat'];
        $lng = (float) $value['lng'];
        $radius = isset($value['radius']) ? (float) $value['radius'] : 10;

        // Bounding box for initial filtering
        $latDelta = $radius / 111.0;
        $lngDelta = $radius / (111.0 * cos(deg2rad($lat)));

        $this->builder->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);

        // Haversine for precise distance on MySQL/PostgreSQL
        $connection = $this->builder->getConnection()->getDriverName();

        if (in_array($connection, ['mysql', 'mariadb', 'pgsql'])) {
            $this->builder->selectRaw("
                *, (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(latitude))
                    )
                ) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc');
        }
    }

    /**
     * Sort by specified field with direction
     * Format: 'field' (asc) or '-field' (desc)
     */
    public function sort($value): void
    {
        $value = trim($value);
        $direction = str_starts_with($value, '-') ? 'desc' : 'asc';
        $column = ltrim($value, '-');

        if (!in_array($column, $this->sortable, true)) {
            $this->builder->orderByDesc('created_at');
            return;
        }

        $this->builder->orderBy($column, $direction);
    }
}
