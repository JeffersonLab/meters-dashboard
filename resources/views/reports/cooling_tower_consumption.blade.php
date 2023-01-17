@extends('layouts.default')

@section('title', $report->title() )

@section('content_header')

@stop

@section('content')
    <h1>{{$report->title()}}</h1>

    <style>
        .report-super {
            text-align: center;
        }
    </style>


    <div class="card report">

        <div class="card-body">
            <table class="meter-data table">

                <thead>
                    <tr>
                        <th colspan="6" class="report-super"><h4>{{$report->beginsAt()}} to {{$report->endsAt()}} </h4></th>
                    </tr>
                    <tr>
                        <th>Cooling Tower</th>
                        <th>Consumed</th>
                        <th>Sewer</th>
                        <th>Evaporation</th>
                        <th>Cycles of Concentration</th>
                        <th>Underlying Meters..</th>
                    </tr>
                </thead>
                <tbody class="tbody-striped">
                @foreach ($report->data() as $datum)
                    <tr>
                        <td>{!!  link_to_route('buildings.show', $datum->label, [$datum->building->id]) !!}</td>

                        <td>
                            {{number_format($datum->consumption,0) }}
                        </td>
                        <td>
                            {{number_format($datum->sewer,0) }}
                        </td>
                        <td>
                            {{number_format($datum->evaporation,0) }}
                        </td>
                        <td>
                            {{number_format($datum->concentration,2) }}
                        </td>
                        <td>
                            {!! link_to_route('reports.item', $datum->label . ' Meters', [
                                'report' => 'water-consumption',
                                'begin'=> $report->beginsAt(),
                                'end' => $report->endsAt(),
                                'meters' => implode(',', $datum->building->meters->pluck('epics_name')->all())
                                ],['target' => '_blank']) !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>


        </div>
    </div>
@stop
