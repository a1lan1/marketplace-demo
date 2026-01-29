<?php

declare(strict_types=1);

namespace App\Enums\Transaction;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';

    case WITHDRAWAL = 'withdrawal';

    case TRANSFER = 'transfer';

    case PURCHASE = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
            self::TRANSFER => 'Transfer',
            self::PURCHASE => 'Purchase',
        };
    }

    /**
     * Get all enum values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
