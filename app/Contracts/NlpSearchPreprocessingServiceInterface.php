<?php

declare(strict_types=1);

namespace App\Contracts;

interface NlpSearchPreprocessingServiceInterface
{
    public function preprocessQuery(string $query): string;
}
