<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Services\Geo\LocationServiceInterface;
use App\DTO\Geo\LocationData;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LocationService implements LocationServiceInterface
{
    /**
     * @return Collection<int, Location>
     */
    public function getLocationsForUser(User $user): Collection
    {
        $key = sprintf('locations_user_%d_list', $user->id);

        return Cache::tags(['locations'])->remember($key, 3600, function () use ($user): Collection {
            return $user->locations()
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->latest()
                ->get();
        });
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
        $key = sprintf('location_%d_stats', $location->id);

        return Cache::tags(['locations'])->remember($key, 3600, function () use ($location): Location {
            return $location->loadCount('reviews')->loadAvg('reviews', 'rating');
        });
    }

    public function deleteLocation(Location $location): void
    {
        $location->delete();
    }
}
