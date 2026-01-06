<?php

declare(strict_types=1);

namespace App\Contracts;

interface RecommendationServiceInterface
{
    /**
     * Get recommended product IDs for a given user.
     *
     * @return array<int>
     */
    public function getRecommendations(int $userId): array;
}
