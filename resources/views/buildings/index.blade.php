

@extends('layouts.default')

@section('title', 'Building Status')

@push('js')

@endpush

@section('content')

<script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
<script>
    window.epicsOptions = {
        "url": "{!! env('EPICSWEB') !!}"
    };
    window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
</script>


<div class="row">
    <div class="col-lg-12">
        <div id="building-alerts"></div>
    </div>
</div>


@stop

@section('css')

@stop

@section('js')
    @include('partials.jsvars')
    <script src="{{ asset('js/building.js') }}"></script>
<script>

</script>
@stop
