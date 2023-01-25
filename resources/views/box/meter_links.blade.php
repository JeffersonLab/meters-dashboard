{{-- Template for displaying a card with list of links

Variables:

$links  - array or iterable collection of links
$title - title for the card

--}}
<div class="card card-solid card-info">
    <div class="card-header with-border">
        <h3 class="card-title">Meters</h3>
    </div>
    <div class="card-body">
        <ul class="links meter-links">
            @foreach ($building->powerMeters->sortBy('epics_name') as $meter)
                <li>{!!  $meter->icon() !!}{!!  link_to_route('meters.show',$meter->epics_name,[$meter->id]) !!}</li>
            @endforeach
            @foreach ($building->waterMeters->sortBy('epics_name') as $meter)
                <li>{!!  $meter->icon() !!}{!!  link_to_route('meters.show',$meter->epics_name,[$meter->id]) !!}</li>
            @endforeach
            @foreach ($building->gasMeters->sortBy('epics_name') as $meter)
                <li>{!!  $meter->icon() !!}{!!  link_to_route('meters.show',$meter->epics_name,[$meter->id]) !!}</li>
            @endforeach
        </ul>
    </div>
</div>

