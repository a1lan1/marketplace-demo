<?php

declare(strict_types=1);

namespace App\Notifications\Seller;

use App\Models\Order;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSaleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public User $seller,
        public Money $payoutAmount
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing('products');

        $sellerProducts = $this->order->products->where('user_id', $this->seller->id);

        return (new MailMessage)
            ->subject('New Sale: Order #'.$this->order->id)
            ->markdown('emails.seller.new_sale', [
                'order' => $this->order,
                'seller' => $this->seller,
                'payoutAmount' => $this->payoutAmount,
                'products' => $sellerProducts,
            ]);
    }
}
