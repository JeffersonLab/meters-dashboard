@extends('layouts.default')

@section('title', 'Substations Summary')

@section('content_header')
    <h1>Substation Summary</h1>
@stop

@section('content')
<style>
    #substation-summary{
        border: none;
    }
</style>
<iframe id="substation-summary" width="1000px" height="780px"
        src="https://epicsweb.jlab.org/wedm/screen?edl=/cs/opshome/edm/meters/substationSummary.edl">
</iframe>
@stop
