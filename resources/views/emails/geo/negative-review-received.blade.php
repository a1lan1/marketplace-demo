<x-mail::message>
# New Negative Review Received

Hello **{{ $seller->name }}**,

You have received a new negative review for your location: **{{ $location->name }}**.

Here are the details:

- **Author:** {{ $review->author_name }}
- **Rating:** {{ $review->rating }} / 5
- **Source:** {{ $review->source->value }}
- **Date:** {{ $review->published_at->format('F j, Y, g:i a') }}

**Review Text:**
> {{ $review->text ?? 'No text provided.' }}

It is recommended to respond to this feedback as soon as possible to address the customer's concerns.

You can view this review in your GeoInsight dashboard.

<x-mail::button :url="route('geo.dashboard')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
