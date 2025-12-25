<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\GeoCollectorServiceInterface;
use App\Models\Feedback;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoCollectorService implements GeoCollectorServiceInterface
{
    public function __construct(protected string $baseUrl, protected int $timeout) {}

    public function sendFeedbackForAnalysis(Feedback $feedback): void
    {
        if (blank($this->baseUrl)) {
            Log::warning('Geo Collector service URL is not configured.');

            return;
        }

        try {
            $payload = [
                [
                    'location_id' => null,
                    'source' => 'internal',
                    'external_id' => (string) $feedback->id,
                    'author_name' => $feedback->author->name,
                    'text' => $feedback->comment,
                    'rating' => $feedback->rating,
                    'published_at' => $feedback->created_at->toIso8601String(),
                ],
            ];

            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->post('collect_reviews', $payload);

            if (! $response->successful()) {
                Log::error('Geo Collector service request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'feedback_id' => $feedback->id,
                ]);
            }
        } catch (Exception $exception) {
            Log::error('Could not connect to Geo Collector service.', [
                'exception' => $exception->getMessage(),
                'feedback_id' => $feedback->id,
            ]);
        }
    }
}
