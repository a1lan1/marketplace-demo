<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property string $message
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Order $order
 * @property-read User $user
 *
 * @method static MessageFactory factory($count = null, $state = [])
 * @method static Builder<static>|Message newModelQuery()
 * @method static Builder<static>|Message newQuery()
 * @method static Builder<static>|Message query()
 * @method static Builder<static>|Message whereCreatedAt($value)
 * @method static Builder<static>|Message whereId($value)
 * @method static Builder<static>|Message whereMessage($value)
 * @method static Builder<static>|Message whereOrderId($value)
 * @method static Builder<static>|Message whereUpdatedAt($value)
 * @method static Builder<static>|Message whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
