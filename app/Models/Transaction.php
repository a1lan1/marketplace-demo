<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $order_id
 * @property Money $amount
 * @property TransactionType $type
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Order|null $order
 * @property-read User $user
 *
 * @method static TransactionFactory factory($count = null, $state = [])
 * @method static Builder<static>|Transaction newModelQuery()
 * @method static Builder<static>|Transaction newQuery()
 * @method static Builder<static>|Transaction query()
 * @method static Builder<static>|Transaction whereAmount($value)
 * @method static Builder<static>|Transaction whereCreatedAt($value)
 * @method static Builder<static>|Transaction whereDescription($value)
 * @method static Builder<static>|Transaction whereId($value)
 * @method static Builder<static>|Transaction whereOrderId($value)
 * @method static Builder<static>|Transaction whereType($value)
 * @method static Builder<static>|Transaction whereUpdatedAt($value)
 * @method static Builder<static>|Transaction whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => MoneyIntegerCast::class,
            'type' => TransactionType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
