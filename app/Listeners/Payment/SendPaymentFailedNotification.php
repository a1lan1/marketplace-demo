<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Events\Payment\PaymentProcessingFailed;
use App\Notifications\Payment\PaymentFailedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentFailedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentProcessingFailed $event): void
    {
        $event->user->notify(new PaymentFailedNotification(
            payment: $event->payment,
            errorMessage: $event->errorMessage
        ));
    }
}
