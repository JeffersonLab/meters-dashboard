@extends('layouts.default')

@section('title', $report->title() )

@section('content_header')
    <h1>{{ $report->title() }}</h1>
    <div class="row">
        <ol class="breadcrumb col-md-12">
            <li><a href="{!! route('reports.index') !!}">Reports</a></li>
            <li class="active">{{$report->title()}}</li>
        </ol>
    </div>

@stop

@section('content')

    <style>
        tr.totals {
            border-top: 2px solid black;
        }
    </style>

    <style>
        tr.totals td {
            font-weight: bold;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Customize</h3>
                </div>

                <div class="box-body">
                    {!!  Form::open(['method'=>'get']) !!}
                    <div class="form-group">
                        @include('partials.daterange',['start' => $report->begins_at, 'end' => $report->ends_at])
                    </div>
                    <div class="form-group">
                        {!! Form::submit('Apply') !!}
                    </div>

                    {!!  Form::close() !!}
                </div>

            </div>

        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Report Data</h3>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="6" style="text-align: center">Goal Buildings</th>
                        </tr>
                        <tr>
                            <th colspan="6" style="text-align: center">
                                Dates: {{$report->begins_at->format('M d Y')}} to {{$report->ends_at->format('M d Y')}}
                            </th>
                        </tr>
                        <tr>
                            <th colspan="6" style="text-align: center">Total degree days: {{$report->degreeDays()}}</th>
                        </tr>
                        <tr>
                            <th>{{ucfirst($report->itemType())}}</th>
                            <th>Total BTU</th>
                            <th>SqFt</th>
                            <th>BTU/SqFt</th>
                            <th>BTU/DegreeDay</th>
                            <th>BTU/SqFt/DegreeDay</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($report->data() as $datum)
                            <tr>
                                <td>
                                    {!! link_to_route('buildings.show', $datum->item->name.' ('.$datum->item->building_num.')', $datum->item->id) !!}
                                </td>

                                @if (isset($datum->btu))
                                    <td>{{number_format($datum->btu,0)}}</td>
                                @else
                                    <td></td>
                                @endif

                                @if (isset($datum->sqFt))
                                    <td>{{number_format($datum->sqFt,0)}}</td>
                                @else
                                    <td></td>
                                @endif
                                @if (isset($datum->btuPerSqFt))
                                    <td>{{number_format($datum->btuPerSqFt,0)}}</td>
                                @else
                                    <td></td>
                                @endif

                                @if (isset($datum->btuPerDD))
                                    <td>{{number_format($datum->btuPerDD,0)}}</td>
                                @else
                                    <td></td>
                                @endif

                                @if (isset($datum->btuPerSqFtPerDD))
                                    <td>{{number_format($datum->btuPerSqFtPerDD,1)}}</td>
                                @else
                                    <td></td>
                                @endif

                            </tr>
                        @endforeach

                        <tr class="totals">
                            <td>Total for Goal Buildings</td>

                            <td>{{number_format($report->totalConsumption()->btu, 0)}}</td>
                            <td>{{number_format($report->totalConsumption()->sqFt, 0)}}</td>
                            <td>{{number_format($report->totalConsumption()->btuPerSqFt,0)}}</td>
                            <td>{{number_format($report->totalConsumption()->btuPerDD, 0)}}</td>
                            <td>{{number_format($report->totalConsumption()->btuPerSqFtPerDD,1)}}</td>
                        </tr>

                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        .box select {
            color: black;
        }

        div.date-range, div.pv-options {
            width: 45%;
            display: inline-block;
            vertical-align: top;
        }

        div.date-range .form-group {
            width: 45%;
            display: inline-block;

        }

        div.pv-options .form-group {
            width: 45%;
            display: inline-block;

        }


    </style>
@stop


@section('js')

    @include('partials.jsvars')

    <script>
        $(document).ready(function () {
            // $('.date-range input').flatpickr({ enableTime: true,  dateFormat: "Y-m-d H:i"});
            $('.chart-select').on('change', jlab.meters.changeChart);
            $('.chart-box').each(jlab.meters.makeChart);
            $('table.meter-data').DataTable({
                "searching": false,
            });
        });

    </script>
@stop
