<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RecommendationServiceInterface;
use Exception;
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
}
