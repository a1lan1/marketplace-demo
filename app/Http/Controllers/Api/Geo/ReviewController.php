<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Geo;

use App\Contracts\Services\Geo\ReviewServiceInterface;
use App\DTO\Geo\ReviewFilterData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Geo\MetricsResource;
use App\Http\Resources\Geo\ReviewResource;
use App\Services\Geo\GeoMetricService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function __construct(
        private readonly GeoMetricService $geoMetricService,
        private readonly ReviewServiceInterface $reviewService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = ReviewFilterData::from($request->all());

        $reviews = $this->reviewService->getReviewsForUser(
            $request->user(),
            $filters
        );

        return ReviewResource::collection($reviews);
    }

    public function metrics(Request $request): MetricsResource
    {
        $metrics = $this->geoMetricService->calculateForUser(
            $request->user(),
            $request->input('location_id') ? (int) $request->input('location_id') : null
        );

        return new MetricsResource($metrics);
    }
}
