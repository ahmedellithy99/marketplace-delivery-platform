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
        'sort',
    ];

    protected array $sortable = [
        'name',
        'base_price',
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
        $this->builder->where('base_price', '>=', $value);
    }

    /**
     * Filter by maximum price
     */
    public function price_max($value): void
    {
        $this->builder->where('base_price', '<=', $value);
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

        // For price sorting, use effective price (base_price for simple/measured, min variant price for variant products)
        if ($column === 'base_price') {
            $this->builder->orderByRaw("
                COALESCE(
                    products.base_price,
                    (SELECT MIN(pv.price) FROM product_variants pv WHERE pv.product_id = products.id)
                ) {$direction}
            ");
            return;
        }

        $this->builder->orderBy($column, $direction);
    }
}
