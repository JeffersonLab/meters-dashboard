
@component('mail::message')

# {{ html()->a(route('alerts.index'), 'Facilities Meters Alerts') }}
## Reported: {{date('Y-m-d H:i')}}


@if ($alerts->count() < 1)

@component('mail::panel')
There are currently no Alerts.
@endcomponent

@else

@component('mail::table')
| Meter          | Status        | Message  |
| :------------- |:-------------:| -------------:|
@foreach($alerts as $alert)
| {{ html()->a(route('meters.show', [$alert->meter()->id]), $alert->meter()->epics_name) }} |<span class="{{$alert->status()}}">{{$alert->status()}}</span> | {{$alert->message()}} |
@endforeach
@endcomponent

@endif




@endcomponent