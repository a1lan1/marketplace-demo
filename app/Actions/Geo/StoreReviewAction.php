<?php

declare(strict_types=1);

namespace App\Actions\Geo;

use App\DTO\Geo\ReviewData;
use App\Enums\Geo\ReviewSentimentEnum;
use App\Events\Geo\NewNegativeReviewReceived;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreReviewAction
{
    /**
     * Store or update a review from external source.
     *
     * @throws Throwable
     */
    public function execute(ReviewData $data): Review
    {
        $review = DB::transaction(function () use ($data) {
            return Review::updateOrCreate(
                [
                    'external_id' => $data->externalId,
                    'source' => $data->source,
                ],
                $data->toArray()
            );
        });

        if ($review->sentiment === ReviewSentimentEnum::NEGATIVE) {
            event(new NewNegativeReviewReceived($review->loadMissing('location.seller')));
        }

        return $review;
    }
}
