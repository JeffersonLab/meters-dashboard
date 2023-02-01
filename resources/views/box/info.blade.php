<div class="card">
    <div class="card-header">
        <h3 class="card-title">Properties</h3>
    </div>
    <div class="card-body">
        <ul>
            @foreach($model->infoBoxItems() as $label => $item)
                <li>{{$label}}: {{ $item }}</li>
            @endforeach
        </ul>
    </div>
</div>

