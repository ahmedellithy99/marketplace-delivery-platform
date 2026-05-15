<?php

namespace App\Exceptions;

use Exception;

class CircularHierarchyException extends Exception
{
    public function __construct(string $message = 'Circular hierarchy detected. A category cannot be its own ancestor.')
    {
        parent::__construct($message);
    }
}
