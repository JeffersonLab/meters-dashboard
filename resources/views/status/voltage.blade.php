<?php

?>

@extends('layouts.default')

@section('title', ucfirst($meterType).' '.$label)


@section('content')
    <script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
    <script>
        //We must definte metersJson before the 'voltage-readouts' div.
        //so that the data will be available to vue when that div is
        //rendered as a component
        window.metersJson = "{!! addslashes(json_encode($data)) !!}";
        window.epicsOptions = {
            "url" : "{!! env('EPICSWEB') !!}"
        };
        window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
    </script>



    <div id="voltage-readouts">

    </div>



@stop
