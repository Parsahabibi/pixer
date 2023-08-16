{{--$order collection is available here--}}

@component('mail::message')
# Payment was falied!

Payment was falied. Please contact with respected authority.
Order tracking id {{$order->tracking_number}}

@component('mail::button', ['url' => $url ])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent