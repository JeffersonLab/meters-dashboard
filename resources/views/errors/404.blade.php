@extends('layouts.default')

@section('title', 'Not Available' )


@section('content')
<h2><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
     The Requested resource is not available.</h2>

<p>{{$exception->getMessage()}}</p>
@stop
