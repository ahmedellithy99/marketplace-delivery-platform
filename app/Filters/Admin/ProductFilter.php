<?php

namespace App\Filters\Admin;

use App\Filters\BaseFilter;

class ProductFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
        'category',
        'is_available',
        'on_discount',
        'sort',
    ];

    protected array $sortable = [
        'name',
        'price',
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
     * Filter by availability status.
     */
    public function is_available(int|string $value): void
    {
        $this->builder->where('is_available', (bool) $value);
    }

    /**
     * Filter by discount presence.
     */
    public function on_discount(int|string $value): void
    {
        if ($value) {
            $this->builder->whereNotNull('discounted_price');
        } else {
            $this->builder->whereNull('discounted_price');
        }
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
