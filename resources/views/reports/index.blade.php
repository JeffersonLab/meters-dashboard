@extends('layouts.default')

@section('title', 'Reports')


@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Reports</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">

                    <h4>Consumption</h4>
                    <ul>
                        <li>{!! Html::meterIcon('power') !!} {!! link_to_route('reports.item','Power Consumption', ['power-consumption']) !!}</li>
                        <li>{!! Html::meterIcon('water') !!} {!! link_to_route('reports.item','Water Consumption', ['water-consumption']) !!}</li>
                        <li>{!! Html::meterIcon('gas') !!} {!! link_to_route('reports.item','Gas Consumption', ['gas-consumption']) !!}</li>
                        <li>{!! Html::meterIcon('cooling-tower') !!}
                            {!! link_to_route('reports.item','Cooling Tower Consumption', ['cooling-tower-consumption']) !!}
                        </li>
                    </ul>

{{--                    <h4>{!! Html::meterIcon('power') !!} Power</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{!! link_to_route('reports.item','Goal Buildings', ['goal-buildings']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Consumption per Building', ['building-power-consumption']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Consumption per Meter', ['meter-power-consumption']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Chart with Data', ['multi-meter', 'meterType'=>'power']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Statistics', ['mya-stats', 'meterType'=>'power']) !!}</li>--}}
{{--                    </ul>--}}

{{--                    <h4>{!! Html::meterIcon('water') !!} Water</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{!! link_to_route('reports.item','Consumption per Building', ['building-water-consumption']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Consumption per Meter', ['meter-water-consumption']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Chart with Data', ['multi-meter', 'meterType'=>'water']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Statistics', ['mya-stats', 'meterType'=>'water']) !!}</li>--}}
{{--                    </ul>--}}

{{--                    <h4>{!! Html::meterIcon('gas') !!} Gas</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{!! link_to_route('reports.item','Consumption per Building', ['building-gas-consumption']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Consumption per Meter', ['meter-gas-consumption']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Chart with Data', ['multi-meter', 'meterType'=>'gas']) !!}</li>--}}
{{--                        <li>{!! link_to_route('reports.item','Statistics', ['mya-stats', 'meterType'=>'gas']) !!}</li>--}}
{{--                    </ul>--}}

{{--                    <h4><i class="fa fa-fw fa-sun text-yellow"></i>Climate</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{!! link_to_route('reports.item','Degree Day Data', ['climate-data']) !!}</li>--}}
{{--                    </ul>--}}

                </div>
                <!-- /.box-body -->

            </div>
            <!-- /.box -->
        </div>
    </div>


@stop
