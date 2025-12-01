<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyDecimalCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property Money $price
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static Builder<static>|OrderProduct newModelQuery()
 * @method static Builder<static>|OrderProduct newQuery()
 * @method static Builder<static>|OrderProduct query()
 * @method static Builder<static>|OrderProduct whereCreatedAt($value)
 * @method static Builder<static>|OrderProduct whereId($value)
 * @method static Builder<static>|OrderProduct whereOrderId($value)
 * @method static Builder<static>|OrderProduct wherePrice($value)
 * @method static Builder<static>|OrderProduct whereProductId($value)
 * @method static Builder<static>|OrderProduct whereQuantity($value)
 * @method static Builder<static>|OrderProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class OrderProduct extends Pivot
{
    protected $table = 'order_product';

    protected function casts(): array
    {
        return [
            'price' => MoneyDecimalCast::class,
        ];
    }
}
