<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTO\UserActivityData;
use App\Enums\UserActivityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserActivityRequest;
use App\Kafka\Producers\UserActivityProducer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserActivityController extends Controller
{
    public function store(StoreUserActivityRequest $request, UserActivityProducer $producer): JsonResponse
    {
        try {
            $dto = new UserActivityData(
                user_id: $request->user()?->id,
                event_type: UserActivityType::from($request->validated('event_type')),
                url: $request->validated('page'),
                ts: now()->format('Y-m-d H:i:s'),
                data: array_merge(['page' => $request->validated('page')], $request->validated('props') ?? []),
            );

            $producer->publish($dto);
        } catch (Throwable $throwable) {
            Log::error('Failed to publish user activity', ['error' => $throwable->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to queue activity',
            ], 500);
        }

        return response()->json(['status' => 'queued'], 202);
    }
}
