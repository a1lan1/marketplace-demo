<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\NlpSearchPreprocessingServiceInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NlpSearchPreprocessingService implements NlpSearchPreprocessingServiceInterface
{
    public function __construct(protected string $baseUrl, protected int $timeout) {}

    public function preprocessQuery(string $query): string
    {
        if (blank($this->baseUrl)) {
            Log::warning('NLP Search Preprocessing service URL is not configured.');

            return $query;
        }

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->post('preprocess_query', ['query' => $query]);

            if ($response->successful()) {
                return $response->json('processed_query', $query);
            }

            Log::error('NLP Search Preprocessing service request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (Exception $exception) {
            Log::error('Could not connect to NLP Search Preprocessing service.', [
                'exception' => $exception->getMessage(),
            ]);
        }

        return $query;
    }
}
