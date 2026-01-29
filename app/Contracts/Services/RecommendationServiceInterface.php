<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface RecommendationServiceInterface
{
    /**
     * Get recommended product IDs for a given user.
     *
     * @return array<int>
     */
    public function getRecommendations(int $userId): array;
}
