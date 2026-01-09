<?php

declare(strict_types=1);

namespace App\Actions\Geo;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\DTO\Geo\ReviewData;
use App\Enums\SentimentEnum;
use App\Events\NegativeSentimentDetected;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreReviewAction
{
    public function __construct(protected ReviewRepositoryInterface $reviewRepository) {}

    /**
     * Store or update a review from external source.
     *
     * @throws Throwable
     */
    public function execute(ReviewData $data): Review
    {
        $review = DB::transaction(function () use ($data): Review {
            return $this->reviewRepository->updateOrCreate($data);
        });

        if ($review->sentiment === SentimentEnum::NEGATIVE) {
            event(new NegativeSentimentDetected($review->loadMissing('location.seller')));
        }

        return $review;
    }
}
