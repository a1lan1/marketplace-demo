<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\Collection;

interface RecommendationServiceInterface
{
    /**
     * Get recommended product IDs for a given user.
     *
     * @return array<int>
     */
    public function getRecommendations(int $userId): array;

    /**
     * Get recommended products for a given user, optionally excluding one.
     */
    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null): Collection;
}
