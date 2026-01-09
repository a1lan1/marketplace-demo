<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\DTO\Geo\LocationData;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function getForUser(User $user): Collection
    {
        return $user->locations()
            ->select(['id', 'seller_id', 'name', 'type', 'address', 'latitude', 'longitude', 'external_ids'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->get();
    }

    public function store(LocationData $data): Location
    {
        return Location::updateOrCreate(
            ['id' => $data->id],
            $data->toArray()
        );
    }

    public function getWithStats(Location $location): Location
    {
        return $location->loadCount('reviews')->loadAvg('reviews', 'rating');
    }

    public function delete(Location $location): void
    {
        $location->delete();
    }
}
