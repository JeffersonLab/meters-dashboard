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

    <div class="row">
        <div class="col-lg-10">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Description</h3>
                </div>
                <div class="box-body">
                    <p>This report allows the user to inspect the degree day climate data that is used for generating
                        climate-normalized reports.  The source column documents the API used to fetch the data.</p>
                    <dt>wunderground</dt>
                    <dd><a target="_blank" href="http://api.wunderground.com/api/">Powered by Weather
                            Underground</a> using the Patrick Henry airport (KPHF) weather station location.
                            This API was discontinued in early 2019 by IBM after it acquired the Weather Channel which
                            previously owned Weather Underground.</dd>
                    <dt>darksky</dt>
                    <dd><a target="_blank" href="https://darksky.net/poweredby/">Powered by Dark Sky</a>
                        using the Patrick Henry airport Latitidue and Longitude (37.1317,-76.4928) for location.
                    </dd>
                    <dt>jlab-weather</dt>
                    <dd><a target="_blank" href="https://www.jlab.org/fm/wx/VWS/data/daily/">Powered by JLab Weather Station</a>
                        using data from station maintained by Jefferson Lab Facilities Management.
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Customize</h3>
                </div>

                <div class="box-body">
                    {!!  Form::open(['method'=>'get']) !!}
                    <div class="form-group">
                        @include('partials.daterange',['start' => $report->beginsAt(), 'end' => $report->endsAt()])
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
                    <table class="climate-data table table-striped">
                        <thead>
                        <tr>
                            <th colspan="5" style="text-align: center">Climate Data</th>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <th>Cooling Degree Days</th>
                            <th>Heating Degree Days</th>
                            <th>Degree Days</th>
                            <th>Source</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach ($report->data() as $datum)

                            <tr>
                                <td>{{$datum->date}}</td>
                                <td>{{number_format($datum->cooling_degree_days,1)}}</td>
                                <td>{{number_format($datum->heating_degree_days,1)}}</td>
                                <td>{{number_format($datum->degree_days,1)}}</td>
                                <td>{{$datum->src}}</td>
                            </tr>
                        @endforeach
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


    </style>
@stop


@section('js')

    @include('partials.jsvars')

    <script>
        $(document).ready(function () {
            $('.date-range input').datepicker({dateFormat: 'yy-mm-dd'});
            $('.chart-select').on('change', jlab.meters.changeChart);
            $('.chart-box').each(jlab.meters.makeChart);
            $('table.climate-data').DataTable({
                "searching": false,
                "pageLength": 25
            });
        });

    </script>
@stop