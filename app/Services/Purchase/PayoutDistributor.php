<?php

declare(strict_types=1);

namespace App\Services\Purchase;

use App\Contracts\Services\BalanceServiceInterface;
use App\DTO\Balance\DepositDTO;
use App\Exceptions\Payment\PayoutException;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Throwable;

readonly class PayoutDistributor
{
    public function __construct(private BalanceServiceInterface $balanceService) {}

    /**
     * @param  Collection<int, User>|null  $sellers
     *
     * @throws Throwable
     */
    public function distribute(Order $order, Collection $sellerPayouts, ?Collection $sellers = null): void
    {
        if (! $sellers instanceof Collection) {
            $sellerIds = $sellerPayouts->keys()->all();
            $sellers = User::whereIn('id', $sellerIds)->get()->keyBy('id');
        }

        foreach ($sellerPayouts as $sellerId => $payoutAmount) {
            /** @var User|null $seller */
            $seller = $sellers->get($sellerId);

            if (! $seller) {
                throw new PayoutException(sprintf('Seller with ID %s not found for order #%d', $sellerId, $order->id));
            }

            $this->balanceService->deposit(
                new DepositDTO(
                    user: $seller,
                    amount: $payoutAmount,
                    order: $order,
                    description: 'Payout for order #'.$order->id
                )
            );
        }
    }
}
