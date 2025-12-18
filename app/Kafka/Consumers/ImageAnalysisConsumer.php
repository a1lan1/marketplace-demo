<?php

declare(strict_types=1);

namespace App\Kafka\Consumers;

use App\Jobs\ProcessImageAnalysisResult;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\Consumer;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Contracts\MessageConsumer;
use Throwable;

class ImageAnalysisConsumer extends Consumer
{
    public function handle(ConsumerMessage $message, MessageConsumer $consumer): void
    {
        try {
            $body = $message->getBody();

            $productId = $body['product_id'] ?? null;
            $analysisResults = $body; // Pass the whole body as analysis results for now

            if (! $productId) {
                Log::error('ImageAnalysisConsumer: Missing product_id in message body.', ['body' => $body]);

                return;
            }

            dispatch(new ProcessImageAnalysisResult((int) $productId, $analysisResults));

            Log::info('Dispatched ProcessImageAnalysisResult job for product ID: '.$productId);
        } catch (Throwable $throwable) {
            Log::error('Error processing Kafka message in ImageAnalysisConsumer: '.$throwable->getMessage(), [
                'exception' => $throwable,
                'message_payload' => (array) $message->getBody(),
            ]);
        }
    }
}
