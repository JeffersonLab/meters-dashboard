@extends('adminlte::page')
@routes()

@section('content_header')
    @if (session()->get('error'))
        <div class="alert alert-danger">
            {{session()->get('error')}}
        </div>
    @endif
    @if (session()->get('success'))
        <div class="alert alert-success">
            {{session()->get('success')}}
        </div>
    @endif
@stop

@push('js')
{{--    <script src="{{asset('js/canvasjs-1.9.10.min.js')}}"></script>--}}
{{--    <script src="{{asset('js/meters.js')}}"></script>--}}
@endpush
