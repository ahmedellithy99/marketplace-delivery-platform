<?php

namespace App\DTOs;

use App\Models\Discount;

class PriceResult
{
    public function __construct(
        public readonly float $unitPrice,
        public readonly float $discountAmount,
        public readonly float $effectivePrice,
        public readonly float $total,
        public readonly ?Discount $discount = null,
    ) {}

    public function hasDiscount(): bool
    {
        return $this->discountAmount > 0;
    }

    public function discountLabel(): ?string
    {
        if (!$this->discount) {
            return null;
        }

        return match ($this->discount->type) {
            'percentage' => $this->discount->value . '%-',
            'fixed' => $this->discount->value . ' جنيه خصم',
            default => null,
        };
    }

    public function toArray(): array
    {
        return [
            'unit_price' => $this->unitPrice,
            'discount_amount' => $this->discountAmount,
            'effective_price' => $this->effectivePrice,
            'total' => $this->total,
            'has_discount' => $this->hasDiscount(),
            'discount_label' => $this->discountLabel(),
        ];
    }
}
