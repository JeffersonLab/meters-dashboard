@extends('layouts.default')

@section('title', $report->title() )

@section('content_header')

@stop

@section('content')

    <div id="consumption-report">

    </div>

@stop

@section('css')

@stop


@section('js')

    @include('partials.jsvars')
    <script src="{{asset('js/report.js')}}"></script>

@stop
