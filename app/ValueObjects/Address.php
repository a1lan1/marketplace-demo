<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

final class Address implements Arrayable
{
    public function __construct(
        public readonly string $country,
        public readonly string $city,
        public readonly string $street,
        public readonly string $houseNumber,
        public readonly string $postalCode,
        public readonly string $fullAddress,
    ) {
        if ($fullAddress === '' || $fullAddress === '0') {
            throw new InvalidArgumentException('Full address cannot be empty.');
        }
    }

    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'city' => $this->city,
            'street' => $this->street,
            'house_number' => $this->houseNumber,
            'postal_code' => $this->postalCode,
            'full_address' => $this->fullAddress,
        ];
    }
}
