<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PaymentMethodRepositoryInterface;
use App\DTO\Payment\PaymentMethodCreateDTO;
use App\Models\PaymentMethod;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function create(PaymentMethodCreateDTO $dto): PaymentMethod
    {
        return PaymentMethod::create($dto->toArray());
    }

    public function findById(string $id): PaymentMethod
    {
        return PaymentMethod::findOrFail($id);
    }
}
