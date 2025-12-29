<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Review;
use App\Models\User;

beforeEach(function (): void {
    $this->seller = User::factory()->withSellerRole()->create();
    $this->location = Location::factory()->create(['seller_id' => $this->seller->id]);
});

test('seller can view reviews for their locations', function (): void {
    Review::factory()->count(5)->create(['location_id' => $this->location->id]);

    // Create review for another location
    $otherLocation = Location::factory()->create();
    Review::factory()->create(['location_id' => $otherLocation->id]);

    $response = $this->actingAs($this->seller)
        ->getJson(route('api.geo.reviews.index'));

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

test('seller can filter reviews by sentiment', function (): void {
    Review::factory()->create([
        'location_id' => $this->location->id,
        'sentiment' => 'positive',
    ]);
    Review::factory()->create([
        'location_id' => $this->location->id,
        'sentiment' => 'negative',
    ]);

    $response = $this->actingAs($this->seller)
        ->getJson(route('api.geo.reviews.index', ['sentiment' => 'positive']));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['sentiment' => 'positive']);
});

test('seller can view metrics', function (): void {
    Review::factory()->create([
        'location_id' => $this->location->id,
        'rating' => 5,
        'sentiment' => 'positive',
    ]);
    Review::factory()->create([
        'location_id' => $this->location->id,
        'rating' => 1,
        'sentiment' => 'negative',
    ]);

    $response = $this->actingAs($this->seller)
        ->getJson(route('api.geo.metrics'));

    $response->assertOk()
        ->assertJson([
            'average_rating' => 3.0,
            'total_reviews' => 2,
            'sentiment_distribution' => [
                'positive' => 1,
                'negative' => 1,
                'neutral' => 0,
            ],
        ]);
});
