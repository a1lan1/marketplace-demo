<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModelClass of Feedback
 *
 * @extends Builder<TModelClass>
 */
class FeedbackBuilder extends Builder
{
    public function forEntity(string $type, int $id): self
    {
        return $this->where('feedbackable_type', $type)
            ->where('feedbackable_id', $id);
    }

    public function forUser(int $userId): self
    {
        return $this->where(function (Builder $query) use ($userId): void {
            $query->forEntity(User::class, $userId);

            $query->orWhereHasMorph(
                'feedbackable',
                [Product::class],
                fn (Builder $productQuery) => $productQuery->where('user_id', $userId)
            );
        });
    }
}
