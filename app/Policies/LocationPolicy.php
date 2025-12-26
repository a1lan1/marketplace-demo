<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSeller();
    }

    public function view(User $user, Location $location): bool
    {
        return $user->id === $location->seller_id;
    }

    public function create(User $user): bool
    {
        return $user->isSeller();
    }

    public function update(User $user, Location $location): bool
    {
        return $user->id === $location->seller_id;
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->id === $location->seller_id;
    }
}
