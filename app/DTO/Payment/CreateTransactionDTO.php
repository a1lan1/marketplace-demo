<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Enums\TransactionType;
use App\Models\User;
use Cknow\Money\Money;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateTransactionDTO extends Data
{
    public function __construct(
        public User $user,
        public Money $amount,
        public TransactionType $type,
        public ?int $orderId = null,
        public ?string $description = null,
    ) {}

    public function forModel(): array
    {
        return [
            'user_id' => $this->user->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'order_id' => $this->orderId,
            'description' => $this->description,
        ];
    }
}
