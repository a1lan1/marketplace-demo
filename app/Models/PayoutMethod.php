<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Payment\PaymentProviderEnum;
use App\Enums\Payment\PaymentTypeEnum;
use Carbon\CarbonImmutable;
use Database\Factories\PayoutMethodFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property PaymentProviderEnum $provider
 * @property string $provider_id
 * @property PaymentTypeEnum $type
 * @property array<array-key, mixed>|null $details
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 *
 * @method static PayoutMethodFactory factory($count = null, $state = [])
 * @method static Builder<static>|PayoutMethod newModelQuery()
 * @method static Builder<static>|PayoutMethod newQuery()
 * @method static Builder<static>|PayoutMethod query()
 * @method static Builder<static>|PayoutMethod whereCreatedAt($value)
 * @method static Builder<static>|PayoutMethod whereDetails($value)
 * @method static Builder<static>|PayoutMethod whereId($value)
 * @method static Builder<static>|PayoutMethod whereProvider($value)
 * @method static Builder<static>|PayoutMethod whereProviderId($value)
 * @method static Builder<static>|PayoutMethod whereType($value)
 * @method static Builder<static>|PayoutMethod whereUpdatedAt($value)
 * @method static Builder<static>|PayoutMethod whereUserId($value)
 *
 * @mixin \Eloquent
 */
class PayoutMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'type',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'type' => PaymentTypeEnum::class,
            'provider' => PaymentProviderEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
