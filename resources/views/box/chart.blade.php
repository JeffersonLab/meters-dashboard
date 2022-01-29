{{-- A chart with a drop-down for changing it

The following variables must be passed when including this view:
    $handle - a unique value to use as DOM id of chart element
    $chartOptions - associative array of the charts the user may choose
    $chartType - the current/default chartOption to use

--}}
<div class="card card-solid card-primary">
    <div class="card-header">
    @if(isset($title))
    <h3 class="card-title">{{$title}}</h3>
        @endif
    @if ($chartOptions && count($chartOptions) > 1)
    <div class="card-tools">
        {!!  Form::label('chart_select','Display:') !!}
        {!!  Form::select('chart_select',$chartOptions, $chartType, [
            'class'=>'chart-select',
            'data-chart' => 'chart-'.$handle
            ]) !!}
    </div>
    @endif
    </div>


    <div class="card-body">
        <div id="chart-{{$handle}}" class="chart-card" data-type="{{$chartType}}"></div>
    </div>


</div>
<!-- /.card -->
