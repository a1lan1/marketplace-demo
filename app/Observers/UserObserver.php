<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\CacheKeyEnum;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    public function saved(User $user): void
    {
        $this->clearCache($user);
    }

    public function deleted(User $user): void
    {
        $this->clearCache($user);
    }

    protected function clearCache(User $user): void
    {
        Cache::tags(['users', 'permissions'])
            ->forget(sprintf(CacheKeyEnum::USER_PERMISSIONS->value, $user->id));
        Cache::tags(['users', 'roles'])
            ->forget(sprintf(CacheKeyEnum::USER_ROLES->value, $user->id));
    }
}
