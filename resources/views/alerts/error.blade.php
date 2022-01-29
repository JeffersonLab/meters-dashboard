@extends('layouts.default')

@section('title', 'Alerts')


@section('content')



    <h1>Alerts</h1>
    <div class="row">
            <div class="col-lg-12">
                <div class="box meters-alert">

                    <!-- /.box-header -->
                    <div class="box-body">
                        <p>An error occurred while attempting to retrieve alert status information.</p>
                        <p>{{$message}}</p>
                    </div>
                </div>
                <!-- /.box -->
            </div>

    </div>





@stop
