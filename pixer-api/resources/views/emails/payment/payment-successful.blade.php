{{--$order collection is available here--}}

@component('mail::message')
# Payment is successful!

Payment was received successfully. Order tracking id {{$order->tracking_number}}

@component('mail::button', ['url' => $url ])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent