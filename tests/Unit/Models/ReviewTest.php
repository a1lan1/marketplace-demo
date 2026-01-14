<?php

use App\DTO\Geo\ReviewFilterData;
use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use App\Models\Location;
use App\Models\Review;
use App\Models\User;

it('can filter reviews for a user (seller)', function (): void {
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();

    $location = Location::factory()->for($seller, 'seller')->create();
    $otherLocation = Location::factory()->for($otherSeller, 'seller')->create();

    Review::factory()->for($location)->create();
    Review::factory()->for($otherLocation)->create();

    $this->assertCount(1, Review::forUser($seller)->get());
});

it('can filter reviews by location', function (): void {
    $location1 = Location::factory()->create();
    $location2 = Location::factory()->create();

    Review::factory()->for($location1)->create();
    Review::factory()->for($location2)->create();

    $this->assertCount(1, Review::forLocation($location1->id)->get());
    $this->assertCount(1, Review::forLocation($location2->id)->get());
});

it('can apply various filters', function (): void {
    $location = Location::factory()->create();

    $review1 = Review::factory()->for($location)->create([
        'source' => ReviewSourceEnum::GOOGLE,
        'sentiment' => SentimentEnum::POSITIVE,
    ]);

    $review2 = Review::factory()->for($location)->create([
        'source' => ReviewSourceEnum::YELP,
        'sentiment' => SentimentEnum::NEGATIVE,
    ]);

    // Test source filter
    $filters = new ReviewFilterData(source: ReviewSourceEnum::GOOGLE);
    $this->assertCount(1, Review::applyFilters($filters)->get());
    expect(Review::applyFilters($filters)->first()->id)->toBe($review1->id);

    // Test sentiment filter
    $filters = new ReviewFilterData(sentiment: SentimentEnum::NEGATIVE);
    $this->assertCount(1, Review::applyFilters($filters)->get());
    expect(Review::applyFilters($filters)->first()->id)->toBe($review2->id);

    // Test combined filters
    $filters = new ReviewFilterData(source: ReviewSourceEnum::GOOGLE, sentiment: SentimentEnum::POSITIVE);
    $this->assertCount(1, Review::applyFilters($filters)->get());

    $filters = new ReviewFilterData(source: ReviewSourceEnum::GOOGLE, sentiment: SentimentEnum::NEGATIVE);
    $this->assertCount(0, Review::applyFilters($filters)->get());
});
