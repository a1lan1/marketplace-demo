<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\FeedbackServiceInterface;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FeedbackService implements FeedbackServiceInterface
{
    public function getFeedbacksForTarget(string $type, int $id): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $key = sprintf('feedbacks_target_%s_%d_page_%s', $type, $id, $page);

        return Cache::tags(['feedbacks'])->remember($key, 3600, function () use ($type, $id): LengthAwarePaginator {
            $modelClass = $this->getFeedbackableModelClass($type);

            return Feedback::query()
                ->where('feedbackable_type', $modelClass)
                ->where('feedbackable_id', $id)
                ->with('author')
                ->latest()
                ->paginate();
        });
    }

    public function getSellerFeedbacks(int $userId): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $key = sprintf('feedbacks_seller_%d_page_%s', $userId, $page);

        return Cache::tags(['feedbacks'])->remember($key, 3600, function () use ($userId): LengthAwarePaginator {
            return Feedback::query()
                ->where(function (Builder $query) use ($userId): void {
                    $query->where(function (Builder $q) use ($userId): void {
                        $q->where('feedbackable_type', User::class)
                            ->where('feedbackable_id', $userId);
                    })
                        ->orWhere(function (Builder $q) use ($userId): void {
                            $q->where('feedbackable_type', Product::class)
                                ->whereHasMorph('feedbackable', [Product::class], function (Builder $productQuery) use ($userId): void {
                                    $productQuery->where('user_id', $userId);
                                });
                        });
                })
                ->with(['author', 'feedbackable'])
                ->latest()
                ->paginate();
        });
    }

    private function getFeedbackableModelClass(string $type): string
    {
        return match ($type) {
            'product' => Product::class,
            'seller' => User::class,
            default => throw new NotFoundHttpException('Feedbackable type not found.'),
        };
    }
}
