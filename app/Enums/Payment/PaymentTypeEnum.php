<?php

declare(strict_types=1);

namespace App\Enums\Payment;

enum PaymentTypeEnum: string
{
    case BALANCE = 'balance';
    case CARD = 'card';
}
