{{--$notice collection is available here--}}

@component('mail::message')
# Title:
{{$notice->notice}}
# Description:
{{$notice->description ?? ''}}
<br>
@if ($action == 'create' )
Notice Created By {{$notice->creator->name ?? ''}}
@elseif($action == 'update')
Notice Updated By {{$notice->creator->name ?? ''}}
@else
Notice deleted By {{$notice->creator->name ?? ''}}
@endif
Untill {{ date('h:i:s a',strtotime($notice->expired_at)) }} {{ date('d F, Y',strtotime($notice->expired_at)) }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
