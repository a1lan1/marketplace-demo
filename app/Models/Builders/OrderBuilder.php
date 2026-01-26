<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Enums\RoleEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @template TModelClass of Order
 *
 * @extends Builder<TModelClass>
 */
class OrderBuilder extends Builder
{
    public function withEssentialRelations(): self
    {
        return $this->select(['id', 'user_id', 'total_amount', 'status', 'created_at'])
            ->with([
                'products' => function (Relation $query): void {
                    $query->select(['products.id', 'products.name', 'products.price'])->with('media');
                },
                'buyer' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
                'payment' => function (Relation $query): void {
                    $query->select(['id', 'order_id', 'amount', 'currency', 'status', 'provider']);
                },
                'transaction' => function (Relation $query): void {
                    $query->select(['id', 'order_id', 'amount', 'type']);
                },
            ]);
    }

    public function forUser(User $user): self
    {
        if ($user->hasRole([RoleEnum::ADMIN, RoleEnum::MANAGER])) {
            return $this;
        }

        return $this->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)
                ->orWhereHas('products.seller', function (Builder $query) use ($user): void {
                    $query->where('id', '!=', $user->id);
                });
        });
    }
}
