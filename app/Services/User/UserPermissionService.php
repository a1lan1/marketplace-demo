<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\UserPermissionServiceInterface;
use App\Models\User;

class UserPermissionService implements UserPermissionServiceInterface
{
    /**
     * @return array<string>
     */
    public function getPermissions(User $user): array
    {
        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * @return array<string>
     */
    public function getRoles(User $user): array
    {
        return $user->getRoleNames()->toArray();
    }
}
