<?php

declare(strict_types=1);

namespace App\Kafka\Producers;

use App\DTO\UserActivityData;
use Exception;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class UserActivityProducer
{
    /**
     * @throws Exception
     */
    public function publish(UserActivityData $data): void
    {
        Kafka::publish(config('kafka.brokers'))
            ->onTopic('user_activity')
            ->withHeaders(['source' => 'frontend'])
            ->withMessage(
                new Message(body: $data->toArray())
            )
            ->send();
    }
}
