<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Sentimentable;
use App\Enums\SentimentEnum;
use App\Models\Builders\FeedbackBuilder;
use App\Observers\FeedbackObserver;
use Carbon\CarbonImmutable;
use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int $id
 * @property int $user_id
 * @property string $feedbackable_type
 * @property int $feedbackable_id
 * @property int $rating
 * @property string|null $comment
 * @property SentimentEnum|null $sentiment
 * @property bool $is_verified_purchase
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $author
 * @property-read Model|\Eloquent $feedbackable
 *
 * @method static FeedbackFactory factory($count = null, $state = [])
 * @method static FeedbackBuilder<static>|Feedback forEntity(string $type, int $id)
 * @method static FeedbackBuilder<static>|Feedback forUser(int $userId)
 * @method static FeedbackBuilder<static>|Feedback withAuthorDetails()
 * @method static FeedbackBuilder<static>|Feedback newModelQuery()
 * @method static FeedbackBuilder<static>|Feedback newQuery()
 * @method static FeedbackBuilder<static>|Feedback query()
 * @method static FeedbackBuilder<static>|Feedback whereComment($value)
 * @method static FeedbackBuilder<static>|Feedback whereCreatedAt($value)
 * @method static FeedbackBuilder<static>|Feedback whereFeedbackableId($value)
 * @method static FeedbackBuilder<static>|Feedback whereFeedbackableType($value)
 * @method static FeedbackBuilder<static>|Feedback whereId($value)
 * @method static FeedbackBuilder<static>|Feedback whereIsVerifiedPurchase($value)
 * @method static FeedbackBuilder<static>|Feedback whereRating($value)
 * @method static FeedbackBuilder<static>|Feedback whereSentiment($value)
 * @method static FeedbackBuilder<static>|Feedback whereUpdatedAt($value)
 * @method static FeedbackBuilder<static>|Feedback whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([FeedbackObserver::class])]
#[UseEloquentBuilder(FeedbackBuilder::class)]
class Feedback extends Model implements Sentimentable
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'feedbackable_id',
        'feedbackable_type',
        'rating',
        'comment',
        'is_verified_purchase',
        'sentiment',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function feedbackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function loadAuthorDetails(): self
    {
        return $this->loadMissing(['author' => function (Relation $query): void {
            $query->select('id', 'name')->with('media');
        }]);
    }

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified_purchase' => 'boolean',
            'sentiment' => SentimentEnum::class,
        ];
    }

    public function getFeedbackableSlug(): ?string
    {
        return match ($this->feedbackable_type) {
            Product::class => 'product',
            User::class => 'seller',
            default => null,
        };
    }

    public function getRecipient(): ?User
    {
        $feedbackable = $this->feedbackable;

        if ($feedbackable instanceof Product) {
            return $feedbackable->seller;
        }

        if ($feedbackable instanceof User) {
            return $feedbackable;
        }

        return null;
    }
}
