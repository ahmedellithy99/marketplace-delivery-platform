<?php

namespace App\Exceptions;

use Exception;

class ProductUnavailableException extends Exception
{
    public function __construct(string $productName)
    {
        parent::__construct(
            "The product '{$productName}' is currently unavailable."
        );
    }
}
