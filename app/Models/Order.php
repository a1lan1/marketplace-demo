<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Models\Builders\OrderBuilder;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id Buyer ID
 * @property Money $total_amount
 * @property OrderStatusEnum $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $buyer
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read Payment|null $payment
 * @property-read OrderProduct|null $pivot
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Transaction|null $transaction
 *
 * @method static OrderFactory factory($count = null, $state = [])
 * @method static OrderBuilder<static>|Order forUser(User $user)
 * @method static OrderBuilder<static>|Order newModelQuery()
 * @method static OrderBuilder<static>|Order newQuery()
 * @method static OrderBuilder<static>|Order query()
 * @method static OrderBuilder<static>|Order whereCreatedAt($value)
 * @method static OrderBuilder<static>|Order whereId($value)
 * @method static OrderBuilder<static>|Order whereStatus($value)
 * @method static OrderBuilder<static>|Order whereTotalAmount($value)
 * @method static OrderBuilder<static>|Order whereUpdatedAt($value)
 * @method static OrderBuilder<static>|Order whereUserId($value)
 * @method static OrderBuilder<static>|Order withEssentialRelations()
 *
 * @mixin \Eloquent
 */
#[UseEloquentBuilder(OrderBuilder::class)]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => MoneyIntegerCast::class,
            'status' => OrderStatusEnum::class,
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(OrderProduct::class)
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function updateStatus(OrderStatusEnum $status): void
    {
        $this->update(['status' => $status]);
    }
}
