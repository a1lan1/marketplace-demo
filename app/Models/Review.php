<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Sentimentable;
use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use App\Observers\ReviewObserver;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $location_id
 * @property ReviewSourceEnum $source
 * @property string $external_id
 * @property string $author_name
 * @property string|null $text
 * @property int $rating
 * @property SentimentEnum|null $sentiment
 * @property CarbonImmutable $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Location $location
 *
 * @method static ReviewFactory factory($count = null, $state = [])
 * @method static Builder<static>|Review newModelQuery()
 * @method static Builder<static>|Review newQuery()
 * @method static Builder<static>|Review query()
 * @method static Builder<static>|Review whereAuthorName($value)
 * @method static Builder<static>|Review whereCreatedAt($value)
 * @method static Builder<static>|Review whereExternalId($value)
 * @method static Builder<static>|Review whereId($value)
 * @method static Builder<static>|Review whereLocationId($value)
 * @method static Builder<static>|Review wherePublishedAt($value)
 * @method static Builder<static>|Review whereRating($value)
 * @method static Builder<static>|Review whereSentiment($value)
 * @method static Builder<static>|Review whereSource($value)
 * @method static Builder<static>|Review whereText($value)
 * @method static Builder<static>|Review whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ReviewObserver::class])]
class Review extends Model implements Sentimentable
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'source',
        'external_id',
        'author_name',
        'text',
        'rating',
        'sentiment',
        'published_at',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected function casts(): array
    {
        return [
            'source' => ReviewSourceEnum::class,
            'sentiment' => SentimentEnum::class,
            'published_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function getRecipient(): ?User
    {
        return $this->location->seller;
    }
}
