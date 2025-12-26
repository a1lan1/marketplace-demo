<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ResponseTemplate;
use App\Models\User;

class ResponseTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSeller();
    }

    public function view(User $user, ResponseTemplate $template): bool
    {
        return $user->id === $template->seller_id;
    }

    public function create(User $user): bool
    {
        return $user->isSeller();
    }

    public function update(User $user, ResponseTemplate $template): bool
    {
        return $user->id === $template->seller_id;
    }

    public function delete(User $user, ResponseTemplate $template): bool
    {
        return $user->id === $template->seller_id;
    }
}
