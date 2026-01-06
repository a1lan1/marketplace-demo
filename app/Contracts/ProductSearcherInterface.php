<?php

declare(strict_types=1);

namespace App\Contracts;

use Laravel\Scout\Builder as ScoutBuilder;

interface ProductSearcherInterface
{
    public function search(string $query): ScoutBuilder;
}
