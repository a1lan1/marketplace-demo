<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class InsufficientFundsException extends Exception
{
    public function __construct(string $message = 'Insufficient funds.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
