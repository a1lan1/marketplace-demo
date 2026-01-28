<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PayoutMethodRepositoryInterface;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PayoutMethodRepository implements PayoutMethodRepositoryInterface
{
    /**
     * @return Collection<int, PayoutMethod>
     */
    public function getForUser(User $user): Collection
    {
        return PayoutMethod::query()
            ->select([
                'id',
                'provider',
                'type',
                'details',
            ])
            ->where('user_id', $user->id)
            ->get();
    }
}
