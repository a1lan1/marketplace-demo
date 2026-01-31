<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Notifications\Admin\PaymentConfigurationErrorNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PaymentConfigurationException extends PaymentException
{
    public function report(): void
    {
        $admins = User::role(RoleEnum::ADMIN->value)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new PaymentConfigurationErrorNotification($this->getMessage()));
        }

        Log::error('Payment Configuration Error: '.$this->getMessage());
    }
}
