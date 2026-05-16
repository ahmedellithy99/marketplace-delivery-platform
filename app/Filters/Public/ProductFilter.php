<?php

namespace App\Filters\Public;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends BaseFilter
{
    protected array $allowed = [
        'search',
        'category',
        'store',
        'price_min',
        'price_max',
        'type',
        'is_available',
        'on_discount',
        'sort',
    ];

    protected array $sortable = [
        'name',
        'price',
        'created_at',
    ];

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        // Base scope: only available, non-soft-deleted products
        $this->builder->where('is_available', true);

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter) && $this->hasValue($value)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    /**
     * Search by product name
     */
    public function search($value): void
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('name', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%");
        });
    }

    /**
     * Filter by category ID
     */
    public function category($value): void
    {
        $this->builder->where('category_id', $value);
    }

    /**
     * Filter by store ID
     */
    public function store($value): void
    {
        $this->builder->where('store_id', $value);
    }

    /**
     * Filter by minimum price
     */
    public function price_min($value): void
    {
        $this->builder->where('price', '>=', $value);
    }

    /**
     * Filter by maximum price
     */
    public function price_max($value): void
    {
        $this->builder->where('price', '<=', $value);
    }

    /**
     * Filter by product type
     */
    public function type($value): void
    {
        $this->builder->where('type', $value);
    }

    /**
     * Filter by availability status
     */
    public function is_available($value): void
    {
        $this->builder->where('is_available', $value);
    }

    /**
     * Filter by discount availability
     */
    public function on_discount($onDiscount = true): void
    {
        if ($onDiscount) {
            $this->builder->whereNotNull('discounted_price');
        } else {
            $this->builder->whereNull('discounted_price');
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
