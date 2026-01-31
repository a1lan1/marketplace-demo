<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayoutMethodPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, PayoutMethod $payoutMethod): bool
    {
        return $user->id === $payoutMethod->user_id;
    }
}
