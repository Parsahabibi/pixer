{{--$order collection is available here--}}

@component('mail::message')
# Your order is processing!

Your order was received successfully. And the order is in processing state.
Your Order tracking id {{$order->tracking_number}}

@component('mail::button', ['url' => $url ])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent