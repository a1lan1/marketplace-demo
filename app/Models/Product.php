<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Cknow\Money\Casts\MoneyDecimalCast;
use Cknow\Money\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $user_id Seller ID
 * @property string $name
 * @property string|null $description
 * @property Money $price
 * @property int $stock
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read string $cover_image
 * @property-read MediaCollection<int, Media> $media
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
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product wherePrice($value)
 * @method static Builder<static>|Product whereStock($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Searchable;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'stock',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'price' => MoneyDecimalCast::class,
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
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price->getAmount(),
            'stock' => $this->stock,
            'cover_image' => $this->cover_image,
        ];
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('product.cover-image-thumb')
            ->crop(400, 400);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product.cover-image')
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
            ->toMediaCollection('product.cover-image');
    }

    protected function coverImage(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->hasMedia('product.cover-image')
                ? $this->getFirstMediaUrl('product.cover-image')
                : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($this->name))).'?s=200&d=identicon';
        })->shouldCache();
    }
}
