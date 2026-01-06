<?php

use App\Actions\Geo\StoreReviewAction;
use App\DTO\Geo\ReviewData;
use App\Enums\Geo\ReviewSourceEnum;
use App\Jobs\ProcessGeoReview;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\mock;

it('can be dispatched', function (): void {
    Bus::fake();

    $data = new ReviewData(
        locationId: 1,
        source: ReviewSourceEnum::GOOGLE,
        externalId: '123',
        authorName: 'John Doe',
        text: 'Great product!',
        rating: 5,
        sentiment: null,
        publishedAt: now()->toDateTimeString()
    );

    dispatch(new ProcessGeoReview($data));

    Bus::assertDispatched(ProcessGeoReview::class);
});

it('calls store review action on handle', function (): void {
    $data = new ReviewData(
        locationId: 1,
        source: ReviewSourceEnum::GOOGLE,
        externalId: '123',
        authorName: 'John Doe',
        text: 'Great product!',
        rating: 5,
        sentiment: null,
        publishedAt: now()->toDateTimeString()
    );

    $actionMock = mock(StoreReviewAction::class);
    $actionMock->shouldReceive('execute')->with($data)->once();

    $job = new ProcessGeoReview($data);
    $job->handle($actionMock);
});
