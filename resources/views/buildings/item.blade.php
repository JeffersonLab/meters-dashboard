@extends('layouts.default')

@section('title', $building->menuLabel())

@section('content_header')
    <h1>{!! $building->icon() !!}{{$building->menuLabel()}}</h1>
@stop

@section('content')

    <style>

        .meter-links li {
            list-style-type: none;
        }

        ul.links.meter-links {
            list-style: none;
            padding-left: 20px;
        }
    </style>

    <script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
    <script>
        window.epicsOptions = {
            "url": "{!! env('EPICSWEB') !!}"
        };
        window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
    </script>

    <div class="row">

        <div class="col-lg-6">
            @include('box.info',['model' => $building])
            @include('box.links',[
                'links' => [$building->linkToEpicsDetailScreen(), $building->linkToCedElement()],
                'title' => 'Related Links'
                ])
        </div>

        <div class="col-lg-6">
            <div id="building-status"></div>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div id="building-charts"></div>
        </div>
    </div>
@stop



@section('js')

    @include('partials.jsvars')
    <script src="{{asset('js/building.js')}}"></script>

@stop

