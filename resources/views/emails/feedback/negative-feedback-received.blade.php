<x-mail::message>
# New Negative Feedback Received

Hello **{{ $recipient->name }}**,

You have received a new negative feedback for: **{{ $item->name }}**.

Here are the details:

- **Author:** {{ $feedback->author->name }}
- **Rating:** {{ $feedback->rating }} / 5
- **Date:** {{ $feedback->created_at->format('F j, Y, g:i a') }}

**Feedback Comment:**
> {{ $feedback->comment }}

It is recommended to respond to this feedback as soon as possible to address the customer's concerns.

<x-mail::button :url="url('/')">
View Feedback
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
