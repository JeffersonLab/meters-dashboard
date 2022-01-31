

@extends('layouts.default')

@section('title', ucfirst($meterType).' Readouts')


@section('content')
    <h1>{{ucfirst($meterType)}} Meter Live Readouts</h1>
    <p>Data on this page have been adjusted so that numbers reflect consumption since the indicated date.  Meters without
    available data are not shown.</p>
    <div class="row">

        @foreach($meters->where('type','=',$meterType) as $meter)
            @if(isset($meter->epics_name))
                <?php $reference = $meter->firstDataOnOrAfter($field, $referenceDate); ?>
                @if($reference)
                    <div class="col-md-4">

                            <div class="card card-solid">
                                <div class="card-header with-border">
                                    <a href="{!! route('meters.show',[$meter->id]) !!}">
                                    <h3 class="card-title">{{$meter->epics_name}}</h3>
                                    </a>
                                </div>
                                <!-- /.card-header -->

                                <div class="card-body">
                                    <label class="odometer-label">{{$label}}: </label>
                                    <div id="gauge-{{$meter->epics_name}}" class="odometer"
                                         data-label="{{$meter->epics_name}}"
                                         data-accumulated="{{$meter->accumulatedRollover($field)}}"
                                         data-startval = "{{$reference->$field}}"
                                         data-pv="{{$meter->epics_name}}:{{$field}}"></div>
                                </div>

                                <!-- /.card-body -->
                                <div class="card-footer">
                                    Since: <b>{{$reference->date->format('Y-m-d')}}</b>
                                    <div id="comm-{{$meter->epics_name}}" class="comm"
                                         data-pv="{{$meter->epics_name}}:commErr"></div>
                                </div>
                                <!-- card-footer -->
                            </div>
                    </div>
                    @endif
            @endif
        @endforeach
    </div>


@stop

@section('css')
    <link rel="stylesheet" href="{!! asset('css/odometer-theme-plaza.css') !!}">
    <style>
        label.odometer-label{
            line-height: 1.5em !important;
            font-size: 24px !important;
        }
        div.odometer{
            line-height: 1.5em !important;
            font-weight: bold !important;
            font-size: 24px !important;
            float:right;
        }
        div.comm{
            min-height: 1.5em;
            float:right;
        }
        div.comm.error{
            color: red;
        }
    </style>
@stop

@section('js')
    <script type="text/javascript" src="{!! asset('js/epics2web.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('js/odometer.min.js') !!}"></script>

    <script>


        window.odometerOptions = {
            auto: false,
            format: '(,ddd)', // Change how digit groups are formatted, and how many digits are shown after the decimal point
            duration: 1000, // Change how long the javascript expects the CSS animation to take
            theme: 'plaza', // Specify the theme (if you have more than one theme css file on the page)
            animation: 'count' // Count is a simpler animation method which just increments the value,
                               // use it when you're looking for something more subtle.
        };

        var gauges = {};
        var comms = {};
        var pvs = [];

        function makeOdometer(domElem){
            console.log($(domElem).attr('id'));
            var odometer = new Odometer({el: $(domElem)[0]});
            odometer.render();
            return odometer;
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
                if (gauges[e.detail.pv]){
                    var elem = gauges[e.detail.pv].el;
                    console.log($(elem).data('label'));
                    console.log($(elem).data('startval'));
                    gauges[e.detail.pv].update(Math.floor(e.detail.value + $(elem).data('accumulated') - $(elem).data('startval')));
                }
                if (comms[e.detail.pv]){
                    commUpdate(e);
                }

            };

            commUpdate = function(e){
                if (e.detail.value > 0) {
                    comms[e.detail.pv].text('Comm Errors: '+ e.detail.value);
                    comms[e.detail.pv].addClass('error');
                }else{
                    comms[e.detail.pv].text('');
                    comms[e.detail.pv].removeClass('error');
                }
            };

            $('.odometer').each(function(){
                console.log($(this).data('pv'));
                gauges[$(this).data('pv')] = makeOdometer($(this));
                pvs.push($(this).data('pv'));
            });

            $('.comm').each(function(){
                console.log($(this).data('pv'));
                comms[$(this).data('pv')] = $(this);
                pvs.push($(this).data('pv'));
            });

            console.log(pvs);
            epicsCon.monitorPvs(pvs);

        });

    </script>

@stop
