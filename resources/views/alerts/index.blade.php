@extends('layouts.default')

@section('title', 'Alerts')


@section('content')

    <style>
        .meters-alert .box-footer {
            font-size: 85%;
            font-weight: lighter;
            text-align: right;
        }
        .meters-alert-down, .meters-alert-critical {
            background-color: lightcoral;
        }

        .meters-alert-warning {
            background-color: yellow;
        }

        .meters-alert-unknown {
            background-color: darkgray;
        }

    </style>


    <h1>Alerts</h1>

    <h2>Hosts</h2>
    <div class="row">
        @if ($hostlist->countNotUp() < 1)
            <div class="col-lg-12">
                <div class="box meters-alert">

                    <!-- /.box-header -->
                    <div class="box-body">
                        <p>There are currently no alerts.</p>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        last checked : {{date('Y-m-d H:i', $hostlist->lastUpdated())}}
                    </div>
                </div>
                <!-- /.box -->
            </div>
        @else
            @foreach($hostlist->hosts()->where('status','!=','up') as $host)
                <div class="col-md-3">
                    <div class="box box-solid meters-alert meters-alert-{{$host->status}}">

                        <div class="box-body">
                            <b><i class="fa fa-fw fa-warning"></i>{{$host->name}} - {{$host->status}}</b>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            last checked : {{date('Y-m-d H:i', $hostlist->unixTime($host->last_check))}}
                        </div>
                        <!-- box-footer -->
                    </div>
                </div>
            @endforeach
        @endif

    </div>

    <h2>Services</h2>

        @if ($servicelist->countNotOk() < 1)
            <div class="row">
            <div class="col-lg-12">
                <div class="box meters-alert">

                    <!-- /.box-header -->
                    <div class="box-body">
                        <p>There are currently no alerts.</p>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        last checked : {{date('Y-m-d H:i', $servicelist->lastUpdated())}}
                    </div>
                </div>
                <!-- /.box -->
            </div>
            </div>
        @else
            @foreach($servicelist->filterByNotStatus('ok') as $hostname => $services)
                <h3>{{$hostname}}</h3>
                <div class="row">

                @foreach ($services as $service)
                    @if ($service->status != 'ok')

                        <div class="col-md-3">
                            <div class="box box-solid meters-alert meters-alert-{{$service->status}}">

                                <div class="box-body">
                                    <b><i class="fa fa-fw fa-warning"></i>{{$service->description}} - {{$service->status}}</b>
                                    <p>{{$service->plugin_output}}</p>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    last checked : {{date('Y-m-d H:i', $servicelist->unixTime($host->last_check))}}
                                </div>
                                <!-- box-footer -->
                            </div>
                        </div>
                    @endif
                @endforeach
                </div>
            @endforeach
        @endif






@stop
