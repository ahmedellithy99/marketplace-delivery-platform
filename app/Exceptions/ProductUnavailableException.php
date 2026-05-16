<?php

namespace App\Exceptions;

use Exception;

class ProductUnavailableException extends Exception
{
    /**
     * @var array<string>
     */
    protected array $unavailableProducts;

    /**
     * @param string|array<string> $products
     */
    public function __construct(string|array $products)
    {
        if (is_string($products)) {
            $this->unavailableProducts = [$products];
            parent::__construct(
                "The product '{$products}' is currently unavailable."
            );
        } else {
            $this->unavailableProducts = $products;
            $names = implode(', ', $products);
            parent::__construct(
                "The following products are currently unavailable: {$names}."
            );
        }
    }

    /**
     * Get the list of unavailable product names.
     *
     * @return array<string>
     */
    public function getUnavailableProducts(): array
    {
        return $this->unavailableProducts;
    }
}
