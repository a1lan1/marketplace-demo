<?php

declare(strict_types=1);

use App\Actions\Geo\StoreReviewAction;
use App\DTO\Geo\ReviewData;
use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use App\Events\NegativeSentimentDetected;
use App\Listeners\SendNegativeSentimentNotification;
use App\Mail\Geo\NegativeReviewReceived;
use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

test('notification is sent when a negative review is stored', function (): void {
    // Arrange
    Mail::fake();
    Event::fake();

    $seller = User::factory()->withSellerRole()->create();
    $location = Location::factory()->create(['seller_id' => $seller->id]);

    $reviewData = ReviewData::from([
        'locationId' => $location->id,
        'source' => ReviewSourceEnum::GOOGLE,
        'externalId' => 'test-ext-id',
        'authorName' => 'John Doe',
        'text' => 'This was a terrible experience.',
        'rating' => 1,
        'sentiment' => SentimentEnum::NEGATIVE,
        'publishedAt' => now()->toIso8601String(),
    ]);

    // Act
    $action = resolve(StoreReviewAction::class);
    $action->execute($reviewData);

    // Assert
    Event::assertDispatched(NegativeSentimentDetected::class);

    $review = Review::first();
    $event = new NegativeSentimentDetected($review);
    $listener = new SendNegativeSentimentNotification;
    $listener->handle($event);

    Mail::assertQueued(NegativeReviewReceived::class, function ($mail) use ($seller) {
        return $mail->hasTo($seller->email);
    });
});

test('notification is not sent for positive review', function (): void {
    // Arrange
    Mail::fake();

    $seller = User::factory()->withSellerRole()->create();
    $location = Location::factory()->create(['seller_id' => $seller->id]);

    $reviewData = ReviewData::from([
        'locationId' => $location->id,
        'source' => ReviewSourceEnum::GOOGLE,
        'externalId' => 'test-ext-id-positive',
        'authorName' => 'Jane Doe',
        'text' => 'This was a great experience!',
        'rating' => 5,
        'sentiment' => SentimentEnum::POSITIVE,
        'publishedAt' => now()->toIso8601String(),
    ]);

    // Act
    $action = resolve(StoreReviewAction::class);
    $action->execute($reviewData);

    // Assert
    Mail::assertNotQueued(NegativeReviewReceived::class);
});
