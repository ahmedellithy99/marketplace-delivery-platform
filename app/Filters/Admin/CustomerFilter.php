<?php

namespace App\Filters\Admin;

use App\Filters\BaseFilter;

class CustomerFilter extends BaseFilter
{
    protected array $allowed = ['search'];

    public function search($value): void
    {
        $this->builder->where(function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('phone', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
        });
    }
}
