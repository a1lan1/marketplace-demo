<?php

use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Http\Resources\Geo\LocationResource;
use App\Models\Location;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\mock;

it('returns locations page with data', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $locations = Location::factory()->count(2)->create(['seller_id' => $user->id]);

    mock(LocationServiceInterface::class, function ($mock) use ($user, $locations): void {
        $mock->shouldReceive('getLocationsForUser')->with($user)->once()->andReturn($locations);
    });

    $expectedLocations = LocationResource::collection($locations)->response()->getData(true);

    get(route('geo.locations.index'))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($expectedLocations): void {
            $page->component('Geo/Locations')
                ->where('locations', $expectedLocations);
        });
});
