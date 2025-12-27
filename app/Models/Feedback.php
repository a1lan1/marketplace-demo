<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Sentimentable;
use App\Enums\SentimentEnum;
use App\Observers\FeedbackObserver;
use Carbon\CarbonImmutable;
use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
 * @method static Builder<static>|Feedback newModelQuery()
 * @method static Builder<static>|Feedback newQuery()
 * @method static Builder<static>|Feedback query()
 * @method static Builder<static>|Feedback whereComment($value)
 * @method static Builder<static>|Feedback whereCreatedAt($value)
 * @method static Builder<static>|Feedback whereFeedbackableId($value)
 * @method static Builder<static>|Feedback whereFeedbackableType($value)
 * @method static Builder<static>|Feedback whereId($value)
 * @method static Builder<static>|Feedback whereIsVerifiedPurchase($value)
 * @method static Builder<static>|Feedback whereRating($value)
 * @method static Builder<static>|Feedback whereSentiment($value)
 * @method static Builder<static>|Feedback whereUpdatedAt($value)
 * @method static Builder<static>|Feedback whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([FeedbackObserver::class])]
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
