<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface NlpSearchPreprocessingServiceInterface
{
    public function preprocessQuery(string $query): string;
}
