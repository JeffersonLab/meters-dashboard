@extends('layouts.default')

@section('title', $report->title() )

@section('content_header')

@stop

@section('content')
    <h1>{{$report->title()}}</h1>
    <style>
        .building-name {
            text-align: center;
        }
        .report h3 {
            text-align: center;
        }
        .incomplete {
            font-style: italic;

        }
        span.incomplete:after {
            font-size: 90%;
            vertical-align: super;
        }
        span.incomplete.end:after{
            content: '\2020';
        }
        span.incomplete.begin:after {
            content: "*";
        }
        .tbody-striped tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.05);
            padding-bottom: 5px;
        }

    </style>

    <div id="consumption-report-filters"></div>

    <div class="card report">
{{--        <div class="card-header">--}}
{{--            <h3>{{$report->pv}}</h3>--}}
{{--        </div>--}}
        <div class="card-body">
        {{--    {{dd($report->data())}}--}}
            <table class="meter-data table">

                <thead>
                @foreach ($report->dataByBuilding() as $building => $data)
                    <tr>
                        <th colspan="5" class="building-name"><h4>{{$building}}</h4></th>
                    </tr>

                    <tr>
                        <th>Meter</th>
                        <th>{{$report->beginsAt()}}</th>
                        <th>{{$report->endsAt()}}</th>
                        <th>{{$report->pv}}</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody class="tbody-striped">
                @foreach ($data as $datum)
                    <tr>
                        <td>{!!  link_to_route('meters.show', $datum->label, [$datum->meter->id]) !!}</td>

                        <td>
                        @if (isset($datum->first))
                            @if ($datum->first->date != $report->begins_at)
                                <span class="incomplete begin">{{number_format($datum->first->{$report->pv},0) }}</span>
                            @else
                                {{number_format($datum->first->{$report->pv},0) }}
                            @endif
                        @endif
                        </td>

                        <td>
                        @if (isset($datum->last))
                            @if ($datum->last->date != $report->ends_at)
                                <span class="incomplete end">{{number_format($datum->last->{$report->pv}, 0) }}</span>
                            @else
                                {{number_format($datum->last->{$report->pv}, 0) }}
                            @endif
                        @endif
                        </td>

                        @if (isset($datum->consumed))
                            <td>{{number_format($datum->consumed, 0)}}</td>
                        @else
                            <td></td>
                        @endif

                        @if (! (isset($datum->first) && isset($datum->last)) )
                            <td> N/A</td>
                        @elseif(! $datum->isComplete)
                            <td>

                                @if ($datum->first->date != $report->begins_at)
                                    <span class="incomplete begin"></span>
                                    {{date('Y-m-d H:i', strtotime($datum->last->date))}}
                                &nbsp;
                                @endif
                                @if ($datum->last->date != $report->ends_at)
                                    <span class="incomplete end"></span>
                                    {{date('Y-m-d H:i', strtotime($datum->last->date))}}

                                @endif
                            </td>
                        @else
                            <td></td>
                        @endif


                    </tr>
                    @endforeach
                </tbody>
                    @endforeach

            </table>


        </div>
    </div>

@stop

@section('css')

@stop


@section('js')

    @include('partials.jsvars')
    <script src="{{asset('js/report.js')}}"></script>

@stop
