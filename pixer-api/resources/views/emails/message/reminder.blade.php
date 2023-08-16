{{--$participant collection is available here--}}

@component('mail::message')
# Conversation Reminder Email
Message: {{ $participant->message->body }}<br>
@component('mail::button', ['url' => $url ])
    View Message
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent
