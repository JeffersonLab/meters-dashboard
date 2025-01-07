@extends('layouts.default')

@section('title', $report->title() )


@section('content_header')
    <h1>{{ $report->title() }}</h1>
    <div class="row">
        <ol class="breadcrumb col-md-12">
            <li><a href="{!! route('reports.index') !!}">Reports</a></li>
            <li class="active">{{ucfirst($report->meterType())}} Meters - Multiple</li>
        </ol>
    </div>

@stop

@section('content')

    <div class="row">
        <div class="col-lg-9 ">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Customize</h3>
                </div>

                <div class="box-body">
                    {{ html()->form('GET', url()->current())->open() }}
                    {{ html()->hidden('meterType', $report->meterType()) }}

                    <div class="form-group">
                        @include('partials.daterange',['start' => $report->beginsAt('Y-m-d H:i'), 'end' => $report->endsAt('Y-m-d H:i')])
                        <div class="pv-options">
                            <div class="form-group">
                                <label for="pv">Option: </label>
                                {{ html()->select('pv', $report->pvOptions(), $request->get('pv'))->class('form-control') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="model_id[]">Meter Selection: </label>
                        {{ html()->multiselect('model_id[]', $report->meterOptions(), $report->meter()->meterIds())->id('models-select')->class('form-control select2') }}
                    </div>

                    <div class="form-group">
                        {{ html()->submit('Apply') }}
                    </div>

                    {{ html()->form()->close() }}
                </div>

            </div>

        </div>

    </div>

    @if ($report->meter()->type())
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-solid box-primary">
                    <div class="box-header">
                        <div class="row">
                        @if($report->title())
                            <div class="col col-lg-9">
                            <h3 class="box-title">{{$report->title()}}</h3>
                            </div>
                            <div class="col col-lg-3 export-links">
                            <a href="#chart-modal" class="chart-option" title="show chart as .png" onclick="showPng()">
                                <i class="fa fa-fw fa-image text-white"></i>
                                Downloadable Chart
                            </a>
                            </div>
                        @endif
                        </div>
                    </div>

                    <div class="box-body">
                        <div id="chart-multimeter" class="chart-box-multimeter"
                             data-type="multi-meter" data-pv="{{$report->chart()->pv()}}"
                             data-models="{{implode(',', $report->meter()->meterIds())}}"></div>
                    </div>


                </div>
            </div>
        </div>
    @endif




    @if ($report->meter()->hasMeters())
    <div class="row">
        <div class="col-lg-12">

            <div class="box box-solid box-primary">
                <div class="box-header">
                    <div class="row">
                    <div class="col col-lg-9">
                        <h3 class="box-title">Chart Data</h3>
                    </div>
                    <div class="col col-lg-3 export-links">
                    @if($report->hasExcel())
                            <a href="{!! $excelUrl !!}"
                               class="chart-option">
                                <i class="fa fa-fw fa-file-excel-o" aria-hidden="true"></i>
                                Download Spreadsheet</a>
                    @endif
                    </div>
                    </div>
                </div>
                <div class="box-body">
                    <table class="multimeter-data table table-striped">
                        <thead>
                        <tr>
                            <th colspan="5" style="text-align: center">Chart Data</th>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <th>Meter</th>
                            <th>{{$report->chart()->pv()}}</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach ($report->data() as $datum)
                            <tr>
                                <td>{{$datum->date}}</td>
                                <td>{{$datum->epics_name}}</td>
                                <td>{{number_format($datum->{$report->chart()->pv()},1)}}</td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif


@stop


@section('modals')
    <div class="modal fade" id="chart-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Chart Image</h4>
                </div>
                <div class="modal-body">
                    <img id="chartImage" class="chart-box-multimeter">
                </div>
                <div class="modal-footer">
                    <p>For copy/download options, righ-click on the chart above.</p>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop


@section('css')
    <style>
        a.chart-option{
            float:right;
        }
        .modal-footer p{
            float:left;
        }
        .modal-dialog{
            width: auto;
        }
        .chart-box-multimeter {
            min-width: 600px;
            min-height: 400px;
        }

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
        div.export-links{
            text-align: right;
            float: right;
        }
        .box-header>div{
            vertical-align: middle;
        }

    </style>
@stop


@section('js')

    @include('partials.jsvars')

    <script>

        function showPng(){
            var base64Image = jlab.multimeters.chart['chart-multimeter'].canvas.toDataURL();
            document.getElementById('chartImage').src = base64Image;
            document.getElementById('chartImage').style.display = 'block';
            $('#chart-modal').modal('show');
        }

        $('.modal').on('show.bs.modal', function () {
            $(this).find('.modal-dialog').css({
                width:'90%', //probably not needed
                height:'auto', //probably not needed
                'background-color': 'green',
                'max-height':'90%'
            });
        });

        $(document).ready(function () {
            // $('.date-range input').flatpickr({ enableTime: true,  dateFormat: "Y-m-d H:i"});
            $('.select2').select2();
            $('#chart-multimeter').each(jlab.multimeters.makeChart);
            $('table.multimeter-data').DataTable({
                "searching": true,
                "pageLength": 50
            });
        });


        jlab.multimeters = jlab.multimeters || {};
        jlab.multimeters.chart = jlab.multimeters.chart || {};

        /**
         * Meters graphing via chartjs
         */
        jlab.multimeters.makeChart = function () {
            var chartId = $(this).attr('id');
            var chartType = $(this).data('type');
            var chartPv = $(this).data('pv');
            var chartModels = $('#models-select').val();
            console.log(chartModels);
            jlab.multimeters.chart[chartId] = new CanvasJS.Chart(chartId);
            jlab.multimeters.getChartOptions(chartType, chartId, chartPv, chartModels);
        };


        jlab.multimeters.getChartOptions = function (chartType, chartId, chartPv, chartModels) {
            console.log(chartModels);
            $.get(jlab.currentApiUrl, {
                    'start': $('input[name="start"]').val(),
                    'end': $('input[name="end"]').val(),
                    'chartType': chartType,
                    'pv': chartPv,
                    'model_id[]': chartModels
                },
                function (response) {
                    if (response.status == 'ok') {
                        jlab.multimeters.chart[chartId].options = response.data;
                        jlab.multimeters.chart[chartId].render();

                    } else {
                        console.log(response);
                    }
                }
            ).fail(function (jqxhr) {
                if (jqxhr.responseJSON) {
                    console.log(jqxhr.responseJSON);
                } else {
                    console.log(jqxhr);
                    alert('unable to obtain chart');
                }
            });
        };


    </script>
@stop
