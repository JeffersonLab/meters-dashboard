

@extends('layouts.default')

@section('title', ucfirst($meterType).' '.$label)


@section('content')

    <style>
        .dyna-green { color: rgb(34, 139, 34);}
        .dyna-amber { color: rgb(218, 165, 32);}
        .dyna-red { color: rgb(255, 0, 0);}
    </style>
    <h1>{{ucfirst($meterType)}} Meter {{$label}} Readouts</h1>
    <p>Legend:
        <span class="dyna-green">Green: from 0 to Average +1 Sigma</span>,
        <span class="dyna-amber">Amber: from +1 Sigma to +2 Sigma</span>
        <span class="dyna-red">Red: greater than +2 Sigma </span>
    </p>
    <p>Statistics are based based on preceding three days of data.  Gauge limit set to 50% over max value recorded during
        the same interval.  Meters without non-zero data for the past three days are not shown.</p>
    <div class="row">
        @foreach($meters as $meter)
            @if(isset($meter->epics_name) && $meter->hasData())
                <?php $stats = $meter->currentStatistics(); ?>
                @if($stats != null && $stats->max > 0)
                    <div class="col-md-3">
                        <a href="{!! route('meters.show',[$meter->id]) !!}">
                            <div class="box box-solid">
                                <div class="box-body">

                                    <div id="gauge-{{$meter->epics_name}}" class="gauge"
                                         data-label="{{$meter->epics_name}}"
                                         data-units="{{$label}}"
                                         data-pv="{{$meter->epics_name}}{{$field}}"
                                         data-max="{{$stats->max}}"
                                         data-avg="{{$stats->avg}}"
                                         data-stddev="{{$stats->stddev}}"
                                    ></div>
                                </div>

                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <span class="stats">Avg={{round($stats->avg,1)}}, Min={{round($stats->min,1)}}, Max={{round($stats->max,1)}}</span>
                                    <span id="{{$meter->epics_name}}{{$field}}"></span><span class="pv-status"></span>
                                </div>
                                <!-- box-footer -->
                            </div>
                        </a>
                    </div>
                    @endif
            @endif
        @endforeach
    </div>





@stop

@section('css')
    <link rel="stylesheet" href="{!! asset('css/jquery.dynameter.css') !!}">
    <style>
        div.gauge{
            display: inline-block;
        }
    </style>
@stop

@section('js')
    <script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('js/jquery.dynameter.js') !!}"></script>
    <script>
        //We must definte metersJson before the 'voltage-readouts' div.
        //so that the data will be available to vue when that div is
        //rendered as a component
        window.metersJson = [];
        window.epicsOptions = {
            "url" : "{!! env('EPICSWEB') !!}"
        };
        window.epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);
    </script>
    <script>

        var gauges = {};
        var pvs = [];

        function makeGauge(domElem){
            var regions = {};
            regions[0] = 'normal';
            regions[$(domElem).data('avg') + $(domElem).data('stddev')] = 'warn';
            regions[$(domElem).data('avg') + 2 * $(domElem).data('stddev')] = 'error';
            return $(domElem).dynameter({
                width: 200,
                label: $(domElem).data('label'),
                value: 0,
                min: 0.0,
                max: $(domElem).data('max') * 1.5,
                unit: $(domElem).data('units'),
                "regions" : regions
            });
        }

        var epicsOptions = {
            "url" : "{!! env('EPICSWEB') !!}"
        };

        $(function () {
            var epicsCon = new jlab.epics2web.ClientConnection(epicsOptions);

            epicsCon.onopen = function (e) {
                epicsCon.monitorPvs(pvs);
            };

            epicsCon.onclose = function(e) {
                //$(".pv-status").text("Disconnected");
                console.log(e);
            };

            epicsCon.onupdate = function (e) {
                console.log(e.detail);
                gauges[e.detail.pv].changeValue(Math.round( e.detail.value * 10 ) / 10);
            };

            $('.gauge').each(function(){
                console.log($(this).data('pv'));
                gauges[$(this).data('pv')] = makeGauge($(this));
                pvs.push($(this).data('pv'));
            });
            console.log(pvs);
            epicsCon.monitorPvs(pvs);

        });

    </script>

@stop