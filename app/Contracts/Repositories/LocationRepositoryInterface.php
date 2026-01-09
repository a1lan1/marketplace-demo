<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Geo\LocationData;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface LocationRepositoryInterface
{
    public function getForUser(User $user): Collection;

    public function store(LocationData $data): Location;

    public function getWithStats(Location $location): Location;

    public function delete(Location $location): void;
}
