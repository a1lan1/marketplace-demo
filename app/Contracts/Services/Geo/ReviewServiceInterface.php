<?php

declare(strict_types=1);

namespace App\Contracts\Services\Geo;

use App\DTO\Geo\ReviewFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewServiceInterface
{
    public function getReviewsForUser(User $user, ReviewFilterData $filters, int $page = 1): LengthAwarePaginator;
}
