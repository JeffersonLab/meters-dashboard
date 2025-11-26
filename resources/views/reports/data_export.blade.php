
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

    <p><b>This report was thrown together quickly and needs further optimization.  Trying to retrieve data for too many meters at once can
        lead to server-side memory exhaustion or exceeding execution time limits.  For best results
        <a href="https://accweb9.acc.jlab.org/apps/meters-dashboard/reports/data-export">execute this report directly
        on accweb9</a> rather than via the ace proxy server.</b></p>
    <div id="consumption-report-filters"></div>

    <div class="card report">

        <div class="card-body">
{{--            @php print \Maatwebsite\Excel\Facades\Excel::raw($report->exportable, \Maatwebsite\Excel\Excel::HTML); @endphp--}}
        </div>
    </div>

@stop

@section('css')

@stop


@section('js')

    @include('partials.jsvars')
    <script src="{{asset('js/report.js')}}"></script>

@stop
