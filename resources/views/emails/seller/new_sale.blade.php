@component('mail::message')
# New Sale: Order #{{ $order->id }}

Hello, {{ $seller->name }}!

Congratulations! You have a new sale in order #{{ $order->id }}.

- **Total Payout for Your Items:** {{ $payoutAmount->format() }}

## Your Sold Items

@include('emails.order.partials.products_table', ['products' => $products])

@component('mail::button', ['url' => route('orders.show', $order)])
View Order
@endcomponent

Thank you for being a seller on our platform!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
