<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\DTO\Payment\PaymentUpdateDTO;
use App\DTO\Payment\ProcessPaymentDTO;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function create(ProcessPaymentDTO $dto): Payment
    {
        return Payment::create($dto->forModel());
    }

    public function updateStatus(Payment $payment, PaymentUpdateDTO $dto): Payment
    {
        $payment->update($dto->getUpdatePayload($payment));

        return $payment;
    }

    public function findByIdempotencyKey(string $key, int $userId): ?Payment
    {
        return Payment::query()
            ->select([
                'id',
                'user_id',
                'status',
                'amount',
                'currency',
                'created_at',
                'metadata',
            ])
            ->where('idempotency_key', $key)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @return Collection<int, PaymentMethod>
     */
    public function getUserPaymentMethods(int $userId): Collection
    {
        return PaymentMethod::query()
            ->where('user_id', $userId)
            ->select(['id', 'type', 'provider', 'provider_id', 'last_four', 'brand', 'expires_at', 'is_default'])
            ->latest()
            ->get();
    }

    public function linkToOrder(string $paymentId, int $orderId): void
    {
        Payment::where('id', $paymentId)->update(['order_id' => $orderId]);
    }
}
