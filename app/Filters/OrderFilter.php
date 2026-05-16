<?php

namespace App\Filters;

class OrderFilter extends BaseFilter
{
    protected array $allowed = [
        'status',
        'search',
        'sort',
    ];

    protected array $sortable = [
        'created_at',
        'total',
    ];

    /**
     * Filter by order status.
     */
    public function status($value): void
    {
        $this->builder->where('status', $value);
    }

    /**
     * Search by order number.
     */
    public function search($value): void
    {
        $this->builder->where('order_number', 'LIKE', "%{$value}%");
    }

    /**
     * Sort by specified field with direction.
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
