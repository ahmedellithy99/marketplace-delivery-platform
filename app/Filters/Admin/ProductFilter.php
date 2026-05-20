<?php

namespace App\Filters\Admin;

use App\Filters\BaseFilter;

class ProductFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
        'category',
        'store',
        'type',
        'is_available',
        'sort',
    ];

    protected array $sortable = [
        'name',
        'base_price',
        'created_at',
    ];

    /**
     * Search by product name or description.
     */
    public function search(string $value): void
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('name', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%");
        });
    }

    /**
     * Filter by category ID.
     */
    public function category(int|string $value): void
    {
        $this->builder->where('category_id', $value);
    }

    /**
     * Filter by store ID.
     */
    public function store(int|string $value): void
    {
        $this->builder->where('store_id', $value);
    }

    /**
     * Filter by product type (simple, variant, measured).
     */
    public function type(string $value): void
    {
        $this->builder->where('type', $value);
    }

    /**
     * Filter by availability status.
     */
    public function is_available(int|string $value): void
    {
        $this->builder->where('is_available', (bool) $value);
    }

    /**
     * Sort by field. Format: 'field' (asc) or '-field' (desc).
     */
    public function sort(string $value): void
    {
        $value     = trim($value);
        $direction = str_starts_with($value, '-') ? 'desc' : 'asc';
        $column    = ltrim($value, '-');

        if (! in_array($column, $this->sortable, true)) {
            $this->builder->orderByDesc('created_at');
            return;
        }

        $this->builder->orderBy($column, $direction);
    }
}
