<?php

declare(strict_types=1);

use App\DTO\UserActivityData;
use App\Enums\UserActivityType;
use App\Kafka\Producers\UserActivityProducer;
use Illuminate\Support\Facades\Config;
use Junges\Kafka\Contracts\MessageProducer;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

it('publishes user activity to the correct topic with DTO payload', function (): void {
    $brokers = 'kafka:9092';
    Config::set('kafka.brokers', $brokers);

    $producer = Mockery::mock(MessageProducer::class);
    $producer->shouldReceive('onTopic')->once()->with('user_activity')->andReturnSelf();
    $producer->shouldReceive('withHeaders')->once()->with(['source' => 'frontend'])->andReturnSelf();
    $producer->shouldReceive('withMessage')->once()->with(Mockery::type(Message::class))->andReturnSelf();
    $producer->shouldReceive('send')->once()->andReturnTrue();

    Kafka::shouldReceive('publish')->once()->with($brokers)->andReturn($producer);

    $dto = new UserActivityData(
        user_id: 1,
        event_type: UserActivityType::CLICK,
        url: '/dashboard',
        ts: now()->format('Y-m-d H:i:s'),
        data: [],
    );

    (new UserActivityProducer)->publish($dto);
});
