<div class="card card-solid card-primary">

    <div class="card-header with-border">
        <h3 class="card-title">Choose Month</h3>
    </div>

    <div class="card-body">
        {{Form::open(['method' => 'get'])}}
        {{Form::selectMonth('month',date('n'))}}
        {{Form::selectRange('year', 2017, date('Y'), date('Y'))}}
        {{Form::submit('Apply')}}
        {{Form::close()}}
    </div>

</div>
<!-- /.card -->
