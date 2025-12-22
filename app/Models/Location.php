<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AddressCast;
use App\Enums\Geo\LocationTypeEnum;
use App\ValueObjects\Address;
use Carbon\CarbonImmutable;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property int $seller_id
 * @property string $name
 * @property Address|null $address
 * @property float $latitude
 * @property float $longitude
 * @property array<array-key, mixed>|null $external_ids
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property LocationTypeEnum $type
 * @property-read User $seller
 *
 * @method static LocationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Location newModelQuery()
 * @method static Builder<static>|Location newQuery()
 * @method static Builder<static>|Location query()
 * @method static Builder<static>|Location whereAddress($value)
 * @method static Builder<static>|Location whereCreatedAt($value)
 * @method static Builder<static>|Location whereExternalIds($value)
 * @method static Builder<static>|Location whereId($value)
 * @method static Builder<static>|Location whereLatitude($value)
 * @method static Builder<static>|Location whereLongitude($value)
 * @method static Builder<static>|Location whereName($value)
 * @method static Builder<static>|Location whereSellerId($value)
 * @method static Builder<static>|Location whereType($value)
 * @method static Builder<static>|Location whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Location extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'seller_id',
        'name',
        'type',
        'address',
        'latitude',
        'longitude',
        'external_ids',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'seller_id' => (string) $this->seller_id,
            'address_full' => $this->address?->fullAddress,
            'address_city' => $this->address?->city,
            'address_street' => $this->address?->street,
            'address_postal_code' => $this->address?->postalCode,
            'address_country' => $this->address?->country,
            '_geo' => [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'type' => LocationTypeEnum::class,
            'address' => AddressCast::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'external_ids' => 'array',
        ];
    }
}
