

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

    </p>

    <div class="row">
        @foreach($meters as $meter)
            @if(isset($meter->epics_name))
                {{--@if($stats != null && $stats->max > 0)--}}
                    <div class="col-md-3">
                        <a href="{!! route('meters.show',[$meter->id]) !!}">
                            <div class="box box-solid">
                                <div class="box-body">
                                    <div id="gauge-{{$meter->epics_name}}" class="gauge"
                                         data-label="{{$meter->epics_name}}"
                                         data-units="{{$label}}"
                                         data-pv="{{$meter->epics_name}}{{$field}}"
                                         data-nominal="{{$meter->voltageParameters()->nominal}}"
                                         data-min="{{$meter->voltageParameters()->min}}"
                                         data-max="{{$meter->voltageParameters()->max}}"
                                         data-lolo="{{$meter->voltageParameters()->lolo}}"
                                         data-low="{{$meter->voltageParameters()->low}}"
                                         data-high="{{$meter->voltageParameters()->high}}"
                                         data-hihi="{{$meter->voltageParameters()->hihi}}"
                                    ></div>

                                </div>

                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <span id="timestamp-{{$meter->epics_name}}"></span><span class="pv-status"></span>
                                </div>
                                <!-- box-footer -->
                            </div>
                        </a>
                    </div>
                    {{--@endif--}}
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
    <script type="text/javascript" src="{!! asset('js/gauge.min.js') !!}"></script>

    <script>

        var gauges = {};
        var pvs = [];


        function makeGauge(domElem){
            let regions = {};
            regions[$(domElem).data('min')] = 'error';
            if ($(domElem).data('low')){
                regions[$(domElem).data('lolo')] = 'warn';
                regions[$(domElem).data('low')] = 'normal';
            }else{
                regions[$(domElem).data('lolo')] = 'normal';
            }

            if ($(domElem).data('high')){
                regions[$(domElem).data('high')] = 'warn';
            }
            regions[$(domElem).data('hihi')] = 'error';
            regions[$(domElem).data('max')] = 'normal';

            console.log(regions);

            return $(domElem).dynameter({
                width: 200,
                label: $(domElem).data('label'),
                value: $(domElem).data('min'),
                min: $(domElem).data('min'),
                max: $(domElem).data('max'),
                unit: $(domElem).data('units'),
                "regions" : regions
            });
        }

        // function makeGauge(domElem){
        //     let staticLabels = {
        //         font: "10px sans-serif",  // Specifies font
        //         color: "#000000",  // Optional: Label text color
        //         fractionDigits: 0  // Optional: Numerical precision. 0=round off.
        //     };
        //
        //     let staticZones = [];
        //     if ($(domElem).data('low')) {
        //         staticLabels.labels = [
        //             $(domElem).data('min'),
        //             $(domElem).data('lolo'),
        //             $(domElem).data('low'),
        //             $(domElem).data('nominal'),
        //             $(domElem).data('high'),
        //             $(domElem).data('hihi'),
        //             $(domElem).data('max')
        //         ];  // Print labels at these values
        //
        //         staticZones = [
        //             {strokeStyle: "#F03E3E", min: $(domElem).data('min'), max: $(domElem).data('lolo')},
        //             {strokeStyle: "#FFDD00", min: $(domElem).data('lolo'), max: $(domElem).data('low')},
        //             {strokeStyle: "#30B32D", min: $(domElem).data('low'), max: $(domElem).data('high')},
        //             {strokeStyle: "#FFDD00", min: $(domElem).data('high'), max: $(domElem).data('hihi')},
        //             {strokeStyle: "#F03E3E", min: $(domElem).data('hihi'), max: $(domElem).data('max')}
        //         ];
        //     } else {
        //         staticLabels.labels = [
        //             $(domElem).data('min'),
        //             $(domElem).data('lolo'),
        //             $(domElem).data('nominal'),
        //             $(domElem).data('hihi'),
        //             $(domElem).data('max')
        //         ];  // Print labels at these values
        //
        //         staticZones = [
        //         {strokeStyle: "#F03E3E", min: $(domElem).data('min'), max: $(domElem).data('lolo')},
        //         {strokeStyle: "#30B32D", min: $(domElem).data('lolo'), max: $(domElem).data('hihi')},
        //         {strokeStyle: "#F03E3E", min: $(domElem).data('hihi'), max: $(domElem).data('max')}
        //         ];
        //     }
        //
        //     //debugger;
        //
        //
        //
        //     let gauge = new Gauge(domElem[ 0 ]).setOptions({
        //         angle: 0.0,
        //         radiusScale: 0.82,
        //         lineWidth: 0.20,
        //         pointer: {
        //             length: 0.5, // // Relative to gauge radius
        //             strokeWidth: 0.035, // The thickness
        //             color: '#000000' // Fill color
        //         },
        //         limitMin: true,
        //         staticZones: staticZones,
        //         staticLabels: staticLabels
        //
        //     });
        //     gauge.epicsReadback = $(document.getElementById("readout-"+$(domElem).data('label')));
        //     gauge.epicsTimestamp = $(document.getElementById("timestamp-"+$(domElem).data('label')));
        //     //gauge.setTextField(document.getElementById("readout-"+$(domElem).data('label')));
        //     gauge.maxValue =$(domElem).data('max'); // set max gauge value
        //     gauge.setMinValue($(domElem).data('min'));  // Prefer setter over gauge.minValue = 0
        //     //debugger;
        //     console.log('hii');
        //     return gauge;
        //
        // }

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
                gauges[e.detail.pv].changeValue(Math.round( e.detail.value));
                //gauges[e.detail.pv].epicsReadback.text(Math.round( e.detail.value ) );
                //gauges[e.detail.pv].epicsTimestamp.text(e.detail.date);
            };

            $('.gauge').each(function(){
                gauges[$(this).data('pv')] = makeGauge($(this));
                pvs.push($(this).data('pv'));
            });
            console.log(pvs);
            epicsCon.monitorPvs(pvs);

        });

    </script>

@stop