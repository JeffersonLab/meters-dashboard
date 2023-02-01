@extends('adminlte::page')
@routes()

@section('content_header')
    @if (session()->get('error'))
        @include('partials.alert',['variant' => 'danger', 'message'=>session()->get('error')])
    @endif
    @if (session()->get('success'))
        @include('partials.alert',['variant' => 'success', 'message'=>session()->get('success')])
    @endif
@stop

@push('js')
{{--    <script src="{{asset('js/canvasjs-1.9.10.min.js')}}"></script>--}}
{{--    <script src="{{asset('js/meters.js')}}"></script>--}}
@endpush
