<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Payment\PaymentProviderEnum;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentCustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property PaymentProviderEnum $provider
 * @property string $provider_customer_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 *
 * @method static PaymentCustomerFactory factory($count = null, $state = [])
 * @method static Builder<static>|PaymentCustomer newModelQuery()
 * @method static Builder<static>|PaymentCustomer newQuery()
 * @method static Builder<static>|PaymentCustomer query()
 * @method static Builder<static>|PaymentCustomer whereCreatedAt($value)
 * @method static Builder<static>|PaymentCustomer whereId($value)
 * @method static Builder<static>|PaymentCustomer whereProvider($value)
 * @method static Builder<static>|PaymentCustomer whereProviderCustomerId($value)
 * @method static Builder<static>|PaymentCustomer whereUpdatedAt($value)
 * @method static Builder<static>|PaymentCustomer whereUserId($value)
 *
 * @mixin \Eloquent
 */
class PaymentCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_customer_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'provider' => PaymentProviderEnum::class,
        ];
    }
}
