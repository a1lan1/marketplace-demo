<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Payment\PaymentMethodCreateDTO;
use App\Models\PaymentMethod;

interface PaymentMethodRepositoryInterface
{
    public function create(PaymentMethodCreateDTO $dto): PaymentMethod;

    public function findById(string $id): PaymentMethod;
}
