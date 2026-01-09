<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Sentimentable;
use App\DTO\Geo\ReviewFilterData;
use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use App\Models\Builders\ReviewBuilder;
use App\Observers\ReviewObserver;
use Carbon\CarbonImmutable;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
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
 * @method static ReviewBuilder<static>|Review applyFilters(ReviewFilterData $filters)
 * @method static ReviewFactory factory($count = null, $state = [])
 * @method static ReviewBuilder<static>|Review forLocation(?int $locationId)
 * @method static ReviewBuilder<static>|Review forUser(User $user)
 * @method static ReviewBuilder<static>|Review newModelQuery()
 * @method static ReviewBuilder<static>|Review newQuery()
 * @method static ReviewBuilder<static>|Review query()
 * @method static ReviewBuilder<static>|Review whereAuthorName($value)
 * @method static ReviewBuilder<static>|Review whereCreatedAt($value)
 * @method static ReviewBuilder<static>|Review whereExternalId($value)
 * @method static ReviewBuilder<static>|Review whereId($value)
 * @method static ReviewBuilder<static>|Review whereLocationId($value)
 * @method static ReviewBuilder<static>|Review wherePublishedAt($value)
 * @method static ReviewBuilder<static>|Review whereRating($value)
 * @method static ReviewBuilder<static>|Review whereSentiment($value)
 * @method static ReviewBuilder<static>|Review whereSource($value)
 * @method static ReviewBuilder<static>|Review whereText($value)
 * @method static ReviewBuilder<static>|Review whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ReviewObserver::class])]
#[UseEloquentBuilder(ReviewBuilder::class)]
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
