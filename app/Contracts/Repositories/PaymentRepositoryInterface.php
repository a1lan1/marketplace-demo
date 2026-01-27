<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTO\Payment\PaymentUpdateDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    public function create(ProcessPaymentDTO $dto): Payment;

    public function updateStatus(Payment $payment, PaymentUpdateDTO $dto): Payment;

    public function findByIdempotencyKey(string $key, int $userId): ?Payment;

    /**
     * @return Collection<int, PaymentMethod>
     */
    public function getUserPaymentMethods(int $userId): Collection;

    public function linkToOrder(string $paymentId, int $orderId): void;
}
