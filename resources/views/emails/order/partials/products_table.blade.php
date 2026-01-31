@component('mail::table')
| Product | Quantity | Price |
|:--------|:--------:|------:|
@foreach ($products as $product)
| {{ $product->name }} | {{ $product->pivot->quantity }} | {{ $product->pivot->line_total->format() }} |
@endforeach
@endcomponent
