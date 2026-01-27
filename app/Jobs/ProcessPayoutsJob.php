<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\SellerPayoutProcessed;
use App\Models\Order;
use App\Services\Purchase\PayoutDistributor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Throwable;

class ProcessPayoutsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public Collection $sellerPayouts
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(PayoutDistributor $payoutDistributor): void
    {
        $payoutDistributor->distribute($this->order, $this->sellerPayouts);

        foreach ($this->sellerPayouts as $sellerId => $payoutAmount) {
            event(new SellerPayoutProcessed(
                $this->order->id,
                $sellerId,
                (int) $payoutAmount->getAmount()
            ));
        }
    }
}
