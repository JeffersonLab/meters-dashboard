{{-- Template for displaying rollover events

Variables:

$model  - Meter or Building

--}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rollover</h3>
    </div>
    <div class="card-body">
        @if ($meter->hasRolloverEvents())
        <ul>
            @foreach($model->rolloverEvents->sortByDesc('rollover_at')->values()->take(5) as $item)
                <li>{{ $item->rollover_at}} - {{$item->rollover_accumulated}} {{$item->field}}</li>
            @endforeach
        </ul>
        @else
            <p>This meter has no rollover history.</p>
        @endif
    </div>
</div>

