<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';

    case MANAGER = 'manager';

    case SELLER = 'seller';

    case BUYER = 'buyer';

    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::SELLER => 'Seller',
            self::BUYER => 'Buyer',
            self::USER => 'User',
        };
    }
}
