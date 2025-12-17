<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RecommendationServiceInterface;
use App\Models\Product;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationService implements RecommendationServiceInterface
{
    public function __construct(private readonly string $baseUrl) {}

    /**
     * Get recommended product IDs for a given user.
     *
     * @return array<int>
     */
    public function getRecommendations(int $userId): array
    {
        try {
            $response = Http::timeout(5)
                ->get(sprintf('%s/recommendations/%d', $this->baseUrl, $userId));

            if ($response->successful()) {
                return $response->json('recommendations') ?? [];
            }

            Log::warning('Recommendation service returned status: '.$response->status(), [
                'user_id' => $userId,
                'body' => $response->body(),
            ]);

            return [];
        } catch (Exception $exception) {
            Log::error('Failed to fetch recommendations: '.$exception->getMessage(), [
                'user_id' => $userId,
            ]);

            return [];
        }
    }

    public function getRecommendedProducts(int $userId, ?int $excludedProductId = null, ?int $limit = 6): Collection
    {
        $recommendedIds = $this->getRecommendations($userId);

        if ($excludedProductId !== null) {
            $recommendedIds = array_diff($recommendedIds, [$excludedProductId]);
        }

        if (empty($recommendedIds)) {
            return new Collection;
        }

        return Product::query()
            ->whereIn('id', $recommendedIds)
            ->orderByRaw('array_position(ARRAY['.implode(',', $recommendedIds).']::bigint[], id::bigint)')
            ->with(['media', 'seller'])
            ->limit($limit)
            ->get();
    }
}
