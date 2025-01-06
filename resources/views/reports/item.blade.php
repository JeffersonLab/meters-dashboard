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
        <div class="col-lg-9">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="box-title">Customize</h3>
                </div>

                <div class="box-body">
                    {{ html()->form('GET', url()->current())->open() }}
                    <div class="form-group">
                        @include('partials.daterange',['start' => $report->begins_at, 'end' => $report->ends_at])
                        <div class="pv-options">
                            <div class="form-group">
                                <label for="pv">Option: </label>
                                {{ html()->select('pv', $report->pvOptions())->class('form-control') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="names">Names: </label>
                        {{ html()->textarea('names', $report->names())->rows(3)->class('form-control') }}
                        <p class="help-block">Use the textarea to restrict the report to the comma-separated list of names.</p>
                    </div>
                    <div class="form-group">
                        {{ html()->submit('Apply') }}
                    </div>

                    {{ html()->form()->close() }}
                </div>

            </div>

        </div>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="box box-solid box-primary">
                <div class="box-header">
                    <h3 class="col-lg-10">Report Data</h3>
                    @if($report->hasExcel())
                    <div class="col-lg-2 export-links">
                        <a href="{!! $excelUrl !!}"
                           class="btn btn-box-tool">
                            <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                            Download Spreadsheet</a>
                    </div>
                    @endif
                </div>
                <div class="box-body">
                    <table class="meter-data table table-striped">
                        <thead>
                        <tr>
                            <th colspan="5" style="text-align: center">{{$report->pv}}</th>
                        </tr>
                        <tr>
                            <th>{{ucfirst($report->itemType())}}</th>
                            <th>{{$report->beginsAt()}}</th>
                            <th>{{$report->endsAt()}}</th>
                            <th>Consumed</th>
                            <th>Note</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach ($report->data() as $datum)

                            <tr>
                                @if (is_a($datum->item, 'App\Meters\Building'))
                                    <td>{!!  link_to_route('buildings.show', $datum->label, [$datum->item->id]) !!}</td>
                                @elseif (is_a($datum->item, 'App\Meters\Meter'))
                                    <td>{!!  link_to_route('meters.show', $datum->label, [$datum->item->id]) !!}</td>
                                @else
                                    <td>{{$datum->label}}</td>
                                @endif

                                @if (isset($datum->first))
                                    <td>{{number_format($datum->first->{$report->pv},0) }}</td>
                                @else
                                    <td></td>
                                @endif

                                @if (isset($datum->last))
                                    <td>{{number_format($datum->last->{$report->pv}, 0) }}</td>
                                @else
                                    <td></td>
                                @endif

                                @if (isset($datum->consumed))
                                    <td>{{number_format($datum->consumed, 0)}}</td>
                                @else
                                    <td></td>
                                @endif

                                @if (! (isset($datum->first) && isset($datum->last)) )
                                    <td> N/A</td>
                                @elseif(! $datum->isComplete)
                                    <td>Incomplete Data: {{date('Y-m-d H:i', strtotime($datum->first->date))}}
                                        to
                                        {{date('Y-m-d H:i', strtotime($datum->last->date))}}</td>
                                @else
                                    <td></td>
                                @endif


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

        div.pv-options .form-group {
            width: 45%;
            display: inline-block;

        }

        div.export-links{
            margin-top:20px;
            margin-bottom: 10px;
            text-align: right;
        }

    </style>
@stop


@section('js')

    @include('partials.jsvars')

    <script>
        $(document).ready(function () {
            //$('.date-range input').datepicker({dateFormat: 'yy-mm-dd'});
            // $('.date-range input').flatpickr({ enableTime: true,  dateFormat: "Y-m-d H:i"});
            $('.chart-select').on('change', jlab.meters.changeChart);
            $('.chart-box').each(jlab.meters.makeChart);
            $('table.meter-data').DataTable({
                "searching": false,
                "pageLength": 50,
                "order": []   // default to server order
            });
        });

    </script>
@stop
