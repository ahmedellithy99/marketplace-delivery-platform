<?php

namespace App\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(string $currentStatus, string $targetStatus)
    {
        parent::__construct(
            "Invalid status transition from '{$currentStatus}' to '{$targetStatus}'."
        );
    }
}
