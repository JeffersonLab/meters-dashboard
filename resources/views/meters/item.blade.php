

@extends('layouts.default')



@section('title', 'Meters')

@section('content_header')
        <h1>{!! $meter->icon() !!}{{$meter->epics_name}}</h1>
@stop

@section('content')

    <script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
    <script>
        window.epicsOptions = {
            "url": "{!! env('EPICSWEB') !!}"
        };
        window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
    </script>

    <div class="row">

        <div class="col-lg-6">
            @include('box.info',['model' => $meter])
            @include('box.links',[
            'links' => [$meter->linkToEpicsDetailScreen(), $meter->linkToCedElement()],
            'title' => 'Related Links'
            ])
        </div>

        <div class="col-lg-6">
            <div id="building-status"></div>
            @if ($meter->hasRolloverEvents())
                @include('box.rollover', ['model' => $meter])
            @endif

            <livewire:meter-limit :meterId="$meter->id" :type="$meter->type" :limit="$meter->meterLimits()->first()"/>

        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
            <div id="building-charts"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div id="data-viewer"></div>
        </div>
    </div>

@stop

@section('css')

@stop


@section('js')

    @include('partials.jsvars')
    <script src="{{asset('js/building.js')}}"></script>
@stop
