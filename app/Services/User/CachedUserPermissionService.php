<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\UserPermissionServiceInterface;
use App\Enums\CacheKeyEnum;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

readonly class CachedUserPermissionService implements UserPermissionServiceInterface
{
    public function __construct(private UserPermissionServiceInterface $service) {}

    /**
     * @return array<string>
     */
    public function getPermissions(User $user): array
    {
        return Cache::tags(['users', 'permissions'])
            ->remember(
                sprintf(CacheKeyEnum::USER_PERMISSIONS->value, $user->id),
                86400,
                fn (): array => $this->service->getPermissions($user)
            );
    }

    /**
     * @return array<string>
     */
    public function getRoles(User $user): array
    {
        return Cache::tags(['users', 'roles'])
            ->remember(
                sprintf(CacheKeyEnum::USER_ROLES->value, $user->id),
                86400,
                fn (): array => $this->service->getRoles($user)
            );
    }
}
