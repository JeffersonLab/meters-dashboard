@extends('layouts.default')

@section('title', 'Test Page')


@section('content')

    <p>EPICSWEB: {!! env('EPICSWEB') !!}</p>

    <script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
    <script>
        window.epicsOptions = {
            "url" : "{!! env('EPICSWEB') !!}"
        };
        window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
    </script>



    <div class="meter-status-table">

    </div>

@stop

@section('js')
    <script src="{{asset('js/test.js')}}"></script>
@stop
