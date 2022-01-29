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
    </div>

    <div class="card-body">
        <table class="meter-data table table-striped">
            <thead>
            <tr>
            @foreach($headings as $heading)
               <th>{{$heading}}</th>
            @endforeach
            </tr>
            </thead>

            {{--{{dd($data)}}--}}
            <tbody>
            @foreach ($data as $datum)
                <tr>
                    @foreach (array_keys($headings) as $col)
                        @if($col == 'date')
                            <td>{{date('Y-m-d H:i', strtotime($datum->$col))}}</td>
                        @else
                            <td>{{$datum->$col}}</td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>


</div>
<!-- /.card -->
