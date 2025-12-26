<?php

declare(strict_types=1);

namespace App\Contracts\Services\Geo;

use App\DTO\Geo\LocationData;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface LocationServiceInterface
{
    /**
     * @return Collection<int, Location>
     */
    public function getLocationsForUser(User $user): Collection;

    public function storeLocation(LocationData $data): Location;

    public function getLocationWithStats(Location $location): Location;

    public function deleteLocation(Location $location): void;
}
