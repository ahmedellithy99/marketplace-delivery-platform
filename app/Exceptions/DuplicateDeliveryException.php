<?php

namespace App\Exceptions;

use Exception;

class DuplicateDeliveryException extends Exception
{
    public function __construct(string $orderNumber)
    {
        parent::__construct(
            "Order '{$orderNumber}' already has an active delivery assignment."
        );
    }
}
