<?php

declare(strict_types=1);

namespace App\Kafka\Consumers;

use App\DTO\Geo\ReviewData;
use App\Enums\Geo\ReviewSourceEnum;
use App\Jobs\ProcessGeoReview;
use App\Jobs\StoreInternalFeedbackSentimentJob;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\Consumer;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Contracts\MessageConsumer;
use Throwable;

class GeoReviewsConsumer extends Consumer
{
    public function handle(ConsumerMessage $message, MessageConsumer $consumer): void
    {
        try {
            $reviewData = ReviewData::from($message->getBody());

            if ($reviewData->source === ReviewSourceEnum::INTERNAL) {
                dispatch(new StoreInternalFeedbackSentimentJob((int) $reviewData->externalId, $reviewData->sentiment));
                Log::info('Dispatched StoreInternalFeedbackSentimentJob for external_id: '.$reviewData->externalId);
            } else {
                dispatch(new ProcessGeoReview($reviewData));
                Log::info('Dispatched ProcessGeoReview job for external_id: '.$reviewData->externalId);
            }
        } catch (Throwable $throwable) {
            Log::error('Error processing Kafka message in GeoReviewsConsumer: '.$throwable->getMessage(), [
                'exception' => $throwable,
                'message_payload' => (array) $message->getBody(),
            ]);
        }
    }
}
