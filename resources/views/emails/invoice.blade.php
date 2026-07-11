@component('mail::message')
# Invoice for Your Order

Dear {{ $order->user->fname }},

Thank you for your order! Below are the details:

**Order Number:** {{ $order->id }}
**Total Amount:** ৳{{ number_format($order->total_amount, 2) }}

@component('mail::table')
| Product       | Quantity     | Price  |
| ------------- |:------------:| ------:|
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ৳{{ number_format($item->price, 2) }} |
@endforeach
@endcomponent

You can track your order using the tracking number: **{{ $order->tracking_number }}**

Thanks for shopping with us!

{{ config('app.name') }}
@endcomponent
