<?php

declare(strict_types=1);

namespace App\Actions\Feedback;

use App\DTO\FeedbackData;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

        $exists = Feedback::where('user_id', $author->id)
            ->where('feedbackable_type', $feedbackableClass)
            ->where('feedbackable_id', $data->feedbackableId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'feedbackable_id' => ['You have already submitted feedback for this item.'],
            ]);
        }

        $isVerified = $this->checkIfVerifiedPurchase($author, $feedbackableClass, $data->feedbackableId);

        return Feedback::create([
            'user_id' => $author->id,
            'feedbackable_type' => $feedbackableClass,
            'feedbackable_id' => $data->feedbackableId,
            'rating' => $data->rating,
            'comment' => $data->comment,
            'is_verified_purchase' => $isVerified,
        ]);
    }

    private function getFeedbackableClass(string $type): string
    {
        return match ($type) {
            'product' => Product::class,
            'seller' => User::class,
            default => throw new InvalidArgumentException('Invalid feedbackable type: '.$type),
        };
    }

    private function checkIfVerifiedPurchase(User $author, string $feedbackableClass, int $feedbackableId): bool
    {
        if ($feedbackableClass !== Product::class) {
            return false;
        }

        return Order::query()
            ->where('user_id', $author->id)
            ->whereHas('products', function ($query) use ($feedbackableId): void {
                $query->where('products.id', $feedbackableId);
            })
            ->exists();
    }
}
