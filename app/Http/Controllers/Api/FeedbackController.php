<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Feedback\CreateFeedbackAction;
use App\Contracts\FeedbackServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Throwable;

class FeedbackController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly FeedbackServiceInterface $feedbackService) {}

    public function index(string $type, int $id): AnonymousResourceCollection
    {
        $feedbacks = $this->feedbackService->getFeedbacksForTarget($type, $id);

        return FeedbackResource::collection($feedbacks);
    }

    public function list(Request $request): AnonymousResourceCollection
    {
        $feedbacks = $this->feedbackService->getSellerFeedbacks($request->user()->id);

        return FeedbackResource::collection($feedbacks);
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(StoreFeedbackRequest $request, CreateFeedbackAction $createFeedbackAction): FeedbackResource
    {
        $this->authorize('create', Feedback::class);

        $feedback = $createFeedbackAction->execute($request->user(), $request->toDto());

        return new FeedbackResource($feedback->load('author'));
    }
}
