<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case REQUIRES_ACTION = 'requires_action';
    case REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    case PROCESSING = 'processing';
}
