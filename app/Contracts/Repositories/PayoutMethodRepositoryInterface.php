<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface PayoutMethodRepositoryInterface
{
    /**
     * @return Collection<int, PayoutMethod>
     */
    public function getForUser(User $user): Collection;

    public function findOrFail(int $id): PayoutMethod;
}
