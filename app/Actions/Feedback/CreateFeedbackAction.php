<?php

declare(strict_types=1);

namespace App\Actions\Feedback;

use App\DTO\FeedbackData;
use App\Enums\OrderStatusEnum;
use App\Events\FeedbackSaved;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class CreateFeedbackAction
{
    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function execute(User $author, FeedbackData $data): Feedback
    {
        $feedbackableClass = $this->getFeedbackableClass($data->feedbackableType);

        $this->ensureFeedbackDoesNotExist($author, $feedbackableClass, $data->feedbackableId);

        $isVerified = $this->checkIfVerifiedPurchase($author, $feedbackableClass, $data->feedbackableId);

        $feedback = Feedback::create([
            'user_id' => $author->id,
            'feedbackable_type' => $feedbackableClass,
            'feedbackable_id' => $data->feedbackableId,
            'rating' => $data->rating,
            'comment' => $data->comment,
            'is_verified_purchase' => $isVerified,
        ]);

        event(new FeedbackSaved($feedback));

        return $feedback;
    }

    private function getFeedbackableClass(string $type): string
    {
        return match ($type) {
            'product' => Product::class,
            'seller' => User::class,
            default => throw new InvalidArgumentException('Invalid feedbackable type: '.$type),
        };
    }

    /**
     * @throws ValidationException
     */
    private function ensureFeedbackDoesNotExist(User $author, string $feedbackableClass, int $feedbackableId): void
    {
        $exists = Feedback::query()
            ->where('user_id', $author->id)
            ->where('feedbackable_type', $feedbackableClass)
            ->where('feedbackable_id', $feedbackableId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'feedbackable_id' => ['You have already submitted feedback for this item.'],
            ]);
        }
    }

    private function checkIfVerifiedPurchase(User $author, string $feedbackableClass, int $feedbackableId): bool
    {
        if ($feedbackableClass !== Product::class) {
            return false;
        }

        return Order::query()
            ->where('user_id', $author->id)
            ->where('status', OrderStatusEnum::COMPLETED)
            ->whereHas('products', function (Builder $query) use ($feedbackableId): void {
                $query->where('products.id', $feedbackableId);
            })
            ->exists();
    }
}
