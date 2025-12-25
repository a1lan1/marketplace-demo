<?php

declare(strict_types=1);

namespace App\Contracts\Services\Geo;

use App\Models\Feedback;

interface GeoCollectorServiceInterface
{
    public function sendFeedbackForAnalysis(Feedback $feedback): void;
}
