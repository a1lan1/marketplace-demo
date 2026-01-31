<?php

declare(strict_types=1);

namespace App\Listeners\Payment;

use App\Events\Payment\PurchaseFailedInsufficientFunds;
use App\Events\Payment\TransferFailedInsufficientFunds;
use App\Events\Payment\WithdrawalFailedInsufficientFunds;
use App\Notifications\Payment\InsufficientFundsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInsufficientFundsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PurchaseFailedInsufficientFunds|WithdrawalFailedInsufficientFunds|TransferFailedInsufficientFunds $event): void
    {
        $user = match (true) {
            $event instanceof TransferFailedInsufficientFunds => $event->sender,
            default => $event->user,
        };

        $user->notify(new InsufficientFundsNotification($event->amount));
    }
}
