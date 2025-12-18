<?php

declare(strict_types=1);

namespace App\Kafka\Producers;

use Exception;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class ImageUploadedProducer
{
    /**
     * @throws Exception
     */
    public function publish(int $productId, string $imageUrl): void
    {
        Kafka::publish(config('kafka.brokers'))
            ->onTopic('product_image_uploaded')
            ->withMessage(
                (new Message(body: [
                    'product_id' => $productId,
                    'image_url' => $imageUrl,
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ]))->withKey((string) $productId)
            )
            ->send();
    }
}
