<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PayoutMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin PayoutMethod
 */
class PayoutMethodResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'type' => $this->type,
            'details' => $this->details,
        ];
    }
}
