
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
            "url" : "{!! env('EPICSWEB') !!}"
        };
        window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
    </script>

    <div class="row">

        <div class="col-lg-4">
            @include('box.info',['model' => $building])
        </div>
        <div class="col-lg-4">
            @include('box.links',[
            'links' => [$building->linkToEpicsDetailScreen(), $building->linkToCedElement()],
            'title' => 'Related Links'
            ])
        </div>
        <div class="col-lg-4">
            <div id="building-monitor"></div>
            <div class="power-meter-status-table"></div>
            <div class="water-meter-status-table"></div>
{{--            @include('box.meter_links')--}}

        </div>
    </div>

    <div class="row">
        <div class="col-lg-12"><hr />
            @include('box.monthyear_selection')
        </div>

    </div>

    @if ($building->hasMeterType('power'))
    <div class="row">
        <div class="col-lg-12">
            @include('box.chart',[
            'title' => 'Power Consumption',
            'handle' => $building->name."-1",
            'chartOptions' => ['dailykwh'=>'kWh','dailymbtu' => 'MBTU','readingskw'=>'kW'],
            'chartType' => 'dailykwh'
            ])
        </div>
    </div>
    @endif

    @if ($building->hasMeterType('water'))
    <div class="row">
        <div class="col-lg-12">
            @include('box.chart',[
            'title' => 'Water Consumption',
            'handle' => $building->name."-2",
            'chartOptions' => ['dailygallons' => 'Gallons','readingsgpm'=>'Flow Rate (GPM)'],
            'chartType' => 'dailyGallons'
            ])
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            @include('box.meter_data',[
                'title' => 'Building Data',
                'headings' => ['date'=>'Date','totkWh'=>'totkWh','totMBTU'=>'totMBTU','gal'=>'Gallons', 'src'=>'Source'],
                'data' => $building->reporter()->dateRangeQuery()->get(),
                ])
        </div>
    </div>

        @stop

@section('css')
    <style>
        .box select{
            color : black;
        }
        .chart-card{
            min-width: 600px;
            min-height: 400px;
        }
    </style>
@stop



@section('js')

    @include('partials.jsvars')
    <script src="{{asset('js/building.js')}}"></script>

    <script>
        $(document).ready(function(){

            $('.chart-select').on('change', jlab.meters.changeChart);
            $('.chart-card').each(jlab.meters.makeChart);
            // $('table.meter-data').DataTable({
            //     "searching": false,
            //     "scrollY": "400px"
            // });

        });

    </script>
@stop

