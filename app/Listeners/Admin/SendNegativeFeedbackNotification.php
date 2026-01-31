<?php

declare(strict_types=1);

namespace App\Listeners\Admin;

use App\Enums\RoleEnum;
use App\Events\NegativeSentimentDetected;
use App\Models\Feedback;
use App\Models\User;
use App\Notifications\Admin\NegativeFeedbackNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendNegativeFeedbackNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NegativeSentimentDetected $event): void
    {
        if ($event->model instanceof Feedback) {
            $feedback = $event->model->loadMissing('author');

            $admins = User::role(RoleEnum::ADMIN->value)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NegativeFeedbackNotification($feedback));
            }
        }
    }
}
