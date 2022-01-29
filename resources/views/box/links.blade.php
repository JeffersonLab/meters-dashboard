{{-- Template for displaying a card with list of links

Variables:

$links  - array or iterable collection of links
$title - title for the card

--}}
<div class="card card-solid card-info">
    <div class="card-header with-border">
        <h3 class="card-title">{{isset($title) ? $title : 'Links'}}</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <ul class="links">
            @foreach ($links as $link)
                <li>{!!  $link !!}</li>
            @endforeach
        </ul>
    </div>
    <!-- /.card-body -->


</div>
<!-- /.card -->
