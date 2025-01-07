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
                        <li>{!! Html::meterIcon('power') !!} {{ html()->a(route('reports.item', ['power-consumption']), 'Power Consumption') }}</li>
                        <li>{!! Html::meterIcon('water') !!} {{ html()->a(route('reports.item', ['water-consumption']), 'Water Consumption') }}</li>
                        <li>{!! Html::meterIcon('gas') !!} {{ html()->a(route('reports.item', ['gas-consumption']), 'Gas Consumption') }}</li>
                        <li>{!! Html::meterIcon('cooling-tower') !!}
                            {{ html()->a(route('reports.item', ['cooling-tower-consumption']), 'Cooling Tower Consumption') }}
                        </li>
                    </ul>

{{--                    <h4>{!! Html::meterIcon('power') !!} Power</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['goal-buildings']), 'Goal Buildings') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['building-power-consumption']), 'Consumption per Building') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['meter-power-consumption']), 'Consumption per Meter') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['multi-meter', 'meterType' => 'power']), 'Chart with Data') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['mya-stats', 'meterType' => 'power']), 'Statistics') }}</li>--}}
{{--                    </ul>--}}

{{--                    <h4>{!! Html::meterIcon('water') !!} Water</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['building-water-consumption']), 'Consumption per Building') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['meter-water-consumption']), 'Consumption per Meter') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['multi-meter', 'meterType' => 'water']), 'Chart with Data') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['mya-stats', 'meterType' => 'water']), 'Statistics') }}</li>--}}
{{--                    </ul>--}}

{{--                    <h4>{!! Html::meterIcon('gas') !!} Gas</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['building-gas-consumption']), 'Consumption per Building') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['meter-gas-consumption']), 'Consumption per Meter') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['multi-meter', 'meterType' => 'gas']), 'Chart with Data') }}</li>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['mya-stats', 'meterType' => 'gas']), 'Statistics') }}</li>--}}
{{--                    </ul>--}}

{{--                    <h4><i class="fa fa-fw fa-sun text-yellow"></i>Climate</h4>--}}
{{--                    <ul>--}}
{{--                        <li>{{ html()->a(route('reports.item', ['climate-data']), 'Degree Day Data') }}</li>--}}
{{--                    </ul>--}}

                </div>
                <!-- /.box-body -->

            </div>
            <!-- /.box -->
        </div>
    </div>


@stop
