{{--$order collection is available here--}}

@component('mail::message')
# Your order was cancelled!

Your order was cancelled! Please contact with authority if needed.
Your Order tracking id {{$order->tracking_number}}

@component('mail::button', ['url' => $url ])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent