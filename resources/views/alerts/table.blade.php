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

    <h2>Nagios Alerts</h2>
    <table class="meter-data table table-striped">
        <thead>
        <tr>
            <th>Status</th>
            <th>Meter</th>
            <th>Message</th>
{{--            <th>Last Check</th>--}}
        </tr>
        </thead>

        {{--{{dd($data)}}--}}
        <tbody>
        @foreach ($serviceAlerts as $alert)
            {{--{{dd($alert)}}--}}
            <tr>
                <td class="meters-alert-{{$alert->status()}}">{{$alert->status()}}</td>
                @if($alert->meter())
                    <td><a href="{{route('meters.show', $alert->meter()->id)}}">
                        {{$alert->meter()->epics_name}}
                        </a>
                    </td>
                @else
                    <td></td>
                @endif
                <td>{!! $alert->message() !!}</td>
{{--                <td>{{date('Y-m-d H:i', $alert->lastCheck())}}</td>--}}
            </tr>
        @endforeach
        </tbody>

    </table>


    <h2>Consumption Warnings</h2>
    <table class="meter-data table table-striped">
        <thead>
        <tr>
            <th>Status</th>
            <th>Meter</th>
            <th>Message</th>
        </tr>
        </thead>

        {{--{{dd($data)}}--}}
        <tbody>
        @foreach ($consumptionAlerts as $alert)
            {{--{{dd($alert)}}--}}
            <tr>
                <td class="meters-alert-{{$alert->status()}}">{{$alert->status()}}</td>
                @if($alert->meter())
                    <td><a href="{{route('meters.show', $alert->meter()->id)}}">
                            {{$alert->meter()->epics_name}}
                        </a>
                    </td>
                @else
                    <td></td>
                @endif
                <td>{!! $alert->message() !!}</td>
            </tr>
        @endforeach
        </tbody>

    </table>


@stop
