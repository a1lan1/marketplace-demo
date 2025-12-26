<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class FeedbackPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }
}
