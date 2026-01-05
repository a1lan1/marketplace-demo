<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface CurrencyServiceInterface
{
    public function getRates(string $base): array;
}
