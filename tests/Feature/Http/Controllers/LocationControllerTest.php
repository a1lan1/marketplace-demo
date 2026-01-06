<?php

use App\Contracts\Services\Geo\LocationServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\mock;

it('returns locations page with data', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $locations = new EloquentCollection(['location1', 'location2']);

    mock(LocationServiceInterface::class, function ($mock) use ($user, $locations): void {
        $mock->shouldReceive('getLocationsForUser')->with($user)->once()->andReturn($locations);
    });

    get(route('geo.locations.index'))
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($locations): void {
            $page->component('Geo/Locations')
                ->where('locations', $locations);
        });
});
