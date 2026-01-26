<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentProviderEnum;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property int $user_id
 * @property string $type
 * @property PaymentProviderEnum $provider
 * @property string $provider_id
 * @property string|null $last_four
 * @property string|null $brand
 * @property CarbonImmutable|null $expires_at
 * @property bool $is_default
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read User $user
 *
 * @method static PaymentMethodFactory factory($count = null, $state = [])
 * @method static Builder<static>|PaymentMethod newModelQuery()
 * @method static Builder<static>|PaymentMethod newQuery()
 * @method static Builder<static>|PaymentMethod onlyTrashed()
 * @method static Builder<static>|PaymentMethod query()
 * @method static Builder<static>|PaymentMethod whereBrand($value)
 * @method static Builder<static>|PaymentMethod whereCreatedAt($value)
 * @method static Builder<static>|PaymentMethod whereDeletedAt($value)
 * @method static Builder<static>|PaymentMethod whereExpiresAt($value)
 * @method static Builder<static>|PaymentMethod whereId($value)
 * @method static Builder<static>|PaymentMethod whereIsDefault($value)
 * @method static Builder<static>|PaymentMethod whereLastFour($value)
 * @method static Builder<static>|PaymentMethod whereProvider($value)
 * @method static Builder<static>|PaymentMethod whereProviderId($value)
 * @method static Builder<static>|PaymentMethod whereType($value)
 * @method static Builder<static>|PaymentMethod whereUpdatedAt($value)
 * @method static Builder<static>|PaymentMethod whereUserId($value)
 * @method static Builder<static>|PaymentMethod withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|PaymentMethod withoutTrashed()
 *
 * @mixin \Eloquent
 */
class PaymentMethod extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'provider_id',
        'last_four',
        'brand',
        'expires_at',
        'is_default',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'is_default' => 'boolean',
            'provider' => PaymentProviderEnum::class,
        ];
    }
}
