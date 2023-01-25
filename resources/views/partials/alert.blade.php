{{-- Template for displaying a card with list of links

Variables:

$variant  - success, danger, warning, info
$message - string to display
--}}
<div class="alert alert-{{$variant}}">
    {{$message}}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
