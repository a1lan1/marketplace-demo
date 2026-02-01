<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'last_four' => $this->last_four,
            'brand' => $this->brand,
            'expires_at' => $this->expires_at?->format('m/Y'),
            'is_default' => $this->is_default,
        ];
    }
}
