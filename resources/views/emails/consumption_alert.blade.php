@component('mail::message')

# {{ html()->a(route('alerts.index'), 'Consumption Warnings') }}
## Reported: {{date('Y-m-d H:i')}}


@component('mail::table')
| Meter          | Status        |
| :------------- |:-------------:|
@foreach($alerts as $alert)
| {{ html()->a(route('meters.show', [$alert->meter()->id]), $alert->meter()->epics_name) }} | <span class="{{$alert->status()}}">{{$alert->status()}}</span> |
| {{$alert->message()}} | |
@endforeach
@endcomponent
@endcomponent
