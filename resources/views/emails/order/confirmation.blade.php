@component('mail::message')
# Order Confirmation #{{ $order->id }}

Hello, {{ $customer->name }}!

Thank you for your order. We have received your payment and your order is now being processed.

- **Order ID:** {{ $order->id }}
- **Total Amount:** {{ $order->total_amount->format() }}
- **Payment Method:** {{ $order->payment_method_display }}

## Your Items

@include('emails.order.partials.products_table', ['products' => $order->products])

@component('mail::button', ['url' => route('orders.show', $order)])
View Order
@endcomponent

Thank you for shopping with us!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
