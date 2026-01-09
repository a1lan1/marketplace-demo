<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Services\Geo\LocationServiceInterface;
use App\DTO\Geo\LocationData;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LocationService implements LocationServiceInterface
{
    public function __construct(protected LocationRepositoryInterface $locationRepository) {}

    /**
     * @return Collection<int, Location>
     */
    public function getLocationsForUser(User $user): Collection
    {
        $key = sprintf('locations_user_%d_list', $user->id);

        return Cache::tags(['locations'])->remember($key, 3600, function () use ($user): Collection {
            return $this->locationRepository->getForUser($user);
        });
    }

    public function storeLocation(LocationData $data): Location
    {
        return $this->locationRepository->store($data);
    }

    public function getLocationWithStats(Location $location): Location
    {
        $key = sprintf('location_%d_stats', $location->id);

        return Cache::tags(['locations'])->remember($key, 3600, function () use ($location): Location {
            return $this->locationRepository->getWithStats($location);
        });
    }

    public function deleteLocation(Location $location): void
    {
        $this->locationRepository->delete($location);
    }
}
