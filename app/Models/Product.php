<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MediaCollection;
use App\Observers\ProductObserver;
use App\Traits\HasFeedback;
use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Laravel\Scout\Searchable;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection as SpatieMediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $user_id Seller ID
 * @property string $name
 * @property string|null $description
 * @property Money $price
 * @property int $stock
 * @property array<array-key, mixed>|null $image_tags
 * @property string|null $image_moderation_status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read string $cover_image
 * @property-read Collection<int, Feedback> $feedbacks
 * @property-read int|null $feedbacks_count
 * @property-read SpatieMediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read User $seller
 *
 * @method static ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereImageModerationStatus($value)
 * @method static Builder<static>|Product whereImageTags($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product wherePrice($value)
 * @method static Builder<static>|Product whereStock($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ProductObserver::class])]
class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasFeedback;
    use InteractsWithMedia;
    use Searchable;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'stock',
        'image_tags',
        'image_moderation_status',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'cover_image',
    ];

    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product): void {
            if (auth()->check()) {
                $product->user_id = auth()->id();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'price' => MoneyIntegerCast::class,
            'image_tags' => 'array',
            'image_moderation_status' => 'string',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing('seller');

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price->getAmount(),
            'stock' => $this->stock,
            'cover_image' => $this->cover_image,
            'seller_name' => $this->seller->name,
            'image_tags' => $this->image_tags,
            'image_moderation_status' => $this->image_moderation_status,
        ];
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(MediaCollection::ProductCoverImageThumb->value)
            ->crop(400, 400);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::ProductCoverImage->value)
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->singleFile();
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function uploadCoverImage(UploadedFile $file): void
    {
        $this->addMedia($file)
            ->usingFileName($file->hashName())
            ->toMediaCollection(MediaCollection::ProductCoverImage->value);
    }

    protected function coverImage(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->hasMedia(MediaCollection::ProductCoverImage->value)
                ? $this->getFirstMediaUrl(MediaCollection::ProductCoverImage->value)
                : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->name))).'?s=200&d=identicon';
        })->shouldCache();
    }
}
