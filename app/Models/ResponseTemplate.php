<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ResponseTemplateObserver;
use Carbon\CarbonImmutable;
use Database\Factories\ResponseTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $seller_id
 * @property string $title
 * @property string $body
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $seller
 *
 * @method static ResponseTemplateFactory factory($count = null, $state = [])
 * @method static Builder<static>|ResponseTemplate newModelQuery()
 * @method static Builder<static>|ResponseTemplate newQuery()
 * @method static Builder<static>|ResponseTemplate query()
 * @method static Builder<static>|ResponseTemplate whereBody($value)
 * @method static Builder<static>|ResponseTemplate whereCreatedAt($value)
 * @method static Builder<static>|ResponseTemplate whereId($value)
 * @method static Builder<static>|ResponseTemplate whereSellerId($value)
 * @method static Builder<static>|ResponseTemplate whereTitle($value)
 * @method static Builder<static>|ResponseTemplate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ResponseTemplateObserver::class])]
class ResponseTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'title',
        'body',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
