<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin User
 */
class UserBalanceResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->balance->getAmount(),
            'formatted' => $this->balance->format(),
            'currency' => $this->balance->getCurrency()->getCode(),
        ];
    }
}
