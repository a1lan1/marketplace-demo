<?php

declare(strict_types=1);

use App\Enums\Geo\LocationTypeEnum;
use App\Models\Location;
use App\Models\User;

beforeEach(function (): void {
    $this->seller = User::factory()->withSellerRole()->create();
    $this->otherSeller = User::factory()->withSellerRole()->create();
});

test('seller can list their locations', function (): void {
    Location::factory()->count(3)->create(['seller_id' => $this->seller->id]);
    Location::factory()->count(2)->create(['seller_id' => $this->otherSeller->id]);

    $response = $this->actingAs($this->seller)
        ->getJson(route('api.geo.locations.index'));

    $response->assertOk()
        ->assertJsonCount(3)
        ->assertJsonStructure([
            '*' => ['id', 'name', 'address', 'latitude', 'longitude', 'external_ids'],
        ]);
});

test('seller can create a location', function (): void {
    $data = [
        'name' => 'Main Store',
        'type' => LocationTypeEnum::STORE->value,
        'address' => [
            'full_address' => '123 Main St, New York, NY',
        ],
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ];

    $response = $this->actingAs($this->seller)
        ->postJson(route('api.geo.locations.store'), $data);

    $response->assertCreated()
        ->assertJsonFragment(['name' => 'Main Store']);

    $this->assertDatabaseHas('locations', [
        'seller_id' => $this->seller->id,
        'name' => 'Main Store',
    ]);
});

test('seller cannot update others location', function (): void {
    $location = Location::factory()->create(['seller_id' => $this->otherSeller->id]);

    $response = $this->actingAs($this->seller)
        ->putJson(route('api.geo.locations.update', $location), [
            'name' => 'Hacked Store',
        ]);

    $response->assertForbidden();
});

test('seller can delete their location', function (): void {
    $location = Location::factory()->create(['seller_id' => $this->seller->id]);

    $response = $this->actingAs($this->seller)
        ->deleteJson(route('api.geo.locations.destroy', $location));

    $response->assertNoContent();
    $this->assertDatabaseMissing('locations', ['id' => $location->id]);
});
