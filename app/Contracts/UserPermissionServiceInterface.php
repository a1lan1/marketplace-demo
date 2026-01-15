<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface UserPermissionServiceInterface
{
    /**
     * @return array<string>
     */
    public function getPermissions(User $user): array;

    /**
     * @return array<string>
     */
    public function getRoles(User $user): array;
}
