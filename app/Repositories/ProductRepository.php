<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTO\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ProductRepository implements ProductRepositoryInterface
{
    public function getPaginated(int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        return Product::query()
            ->select(['id', 'user_id', 'name', 'description', 'price', 'stock', 'created_at', 'updated_at'])
            ->with([
                'seller' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getForUser(User $user, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return $user->products()
            ->select(['id', 'user_id', 'name', 'description', 'price', 'stock', 'created_at', 'updated_at'])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function store(ProductDTO $productDTO): Product
    {
        /** @var Product $product */
        $product = $productDTO->user->products()->updateOrCreate(
            ['id' => $productDTO->productId],
            $productDTO->toArray()
        );

        if ($productDTO->coverImage instanceof UploadedFile) {
            $product->uploadCoverImage($productDTO->coverImage);
        }

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function getRecommended(array $ids, int $limit = 6): Collection
    {
        return Product::query()
            ->recommended($ids)
            ->select(['id', 'user_id', 'name', 'price', 'stock'])
            ->with([
                'media',
                'seller' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->limit($limit)
            ->get();
    }

    public function getByIdsLocked(array $ids): Collection
    {
        return Product::with('seller')
            ->whereIn('id', $ids)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function searchPaginated(string $query, int $perPage = 12, int $page = 1): LengthAwarePaginator
    {
        return Product::search($query)->paginate($perPage, 'page', $page);
    }

    public function searchSuggestions(string $query, int $limit = 5): Collection
    {
        return Product::search($query)
            ->take($limit)
            ->get();
    }
}
