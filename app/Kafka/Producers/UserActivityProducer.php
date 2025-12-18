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
        $producer = Kafka::publish(config('kafka.brokers'))
            ->onTopic('user_activity')
            ->withHeaders(['source' => 'frontend']);

        $message = new Message(body: $data->toArray());

        if ($data->user_id) {
            $message->withKey((string) $data->user_id);
        }

        $producer->withMessage($message)->send();
    }
}
