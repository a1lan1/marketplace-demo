<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\LocationServiceInterface;
use App\DTO\Geo\LocationData;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LocationService implements LocationServiceInterface
{
    public function getLocationsForUser(User $user): Collection
    {
        return $user->locations()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->get();
    }

    public function storeLocation(LocationData $data): Location
    {
        return Location::updateOrCreate(
            ['id' => $data->id],
            $data->toArray()
        );
    }

    public function getLocationWithStats(Location $location): Location
    {
        return $location->loadCount('reviews')->loadAvg('reviews', 'rating');
    }

    public function deleteLocation(Location $location): void
    {
        $location->delete();
    }
}
