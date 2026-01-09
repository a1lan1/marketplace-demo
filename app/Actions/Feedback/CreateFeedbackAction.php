<?php

declare(strict_types=1);

namespace App\Actions\Feedback;

use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\DTO\FeedbackData;
use App\Events\FeedbackSaved;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class CreateFeedbackAction
{
    public function __construct(protected FeedbackRepositoryInterface $feedbackRepository) {}

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function execute(User $author, FeedbackData $data): Feedback
    {
        $feedbackableClass = $this->getFeedbackableClass($data->feedbackableType);

        $this->ensureFeedbackDoesNotExist($author, $feedbackableClass, $data->feedbackableId);

        $isVerified = $this->checkIfVerifiedPurchase($author, $feedbackableClass, $data->feedbackableId);

        $feedback = $this->feedbackRepository->store($author, $feedbackableClass, $data, $isVerified);

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
        $exists = $this->feedbackRepository->existsForUserAndEntity($author->id, $feedbackableClass, $feedbackableId);

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
