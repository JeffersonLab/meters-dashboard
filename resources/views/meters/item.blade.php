

@extends('layouts.default')

@section('title', 'Meters')

@section('content_header')
    <h1>{!! $meter->icon() !!}{{$meter->epics_name}}</h1>
    <div class="row">
        <ol class="breadcrumb col-md-12">
            <li><a href="{!! route('meters.index') !!}" >Meters</a></li>
            <li class="active">{{$meter->name}}</li>
        </ol>
    </div>

@stop

@section('content')


    <div class="row">

    <div class="col-lg-6">
        @include('box.info',['model' => $meter])
    </div>
    <div class="col-lg-6">
        @include('box.links',[
        'links' => [$meter->linkToEpicsDetailScreen(), $meter->linkToCedElement()],
        'title' => 'Related Links'
        ])
    </div>
    </div>

    <div class="row">
        <div class="col-lg-12"><hr />
            @include('box.monthyear_selection')
        </div>

    </div>

    @if ($meter->type == 'power')
        @include('meters.power')
    @endif
    @if ($meter->type == 'water')
        @include('meters.water')
    @endif
    @if ($meter->type == 'gas')
        @include('meters.gas')
    @endif


        @stop

@section('css')
<style>
    .box select{
        color : black;
    }
    .chart-box{
        min-width: 600px;
        min-height: 400px;
    }
</style>
@stop


@section('js')

    @include('partials.jsvars')

    <script>
        $(document).ready(function(){

         $('.chart-select').on('change', jlab.meters.changeChart);
         $('.chart-box').each(jlab.meters.makeChart);
         $('table.meter-data').DataTable({
             "searching": false,
             "scrollY": "400px"
         });
        });

    </script>
@stop