<div class="card meter-limits">
    <div class="card-header">
        <h3 class="card-title">Alert Thresholds</h3>
        @if(Auth::user())
        @if($limit)
            <i class="fa fa-edit" style="float:right" wire:click.prevent="toggleEdit"></i>
        @endif
        @endif
    </div>
    <div class="card-body">
        @if ($limit || $isEditable)
        <table class="table">
            <tr>
                <th>Units</th>
                <th>LoLo</th>
                <th>Low</th>
                <th>High</th>
                <th>HiHi</th>
            </tr>
            @if ($limit)
            <tr>
                <td>{{$label}}</td>
                <td>{{$limit->lolo === null ? 'NA' : $limit->lolo}}</td>
                <td>{{$limit->low === null ? 'NA' : $limit->low}}</td>
                <td>{{$limit->high === null ? 'NA' : $limit->high}}</td>
                <td>{{$limit->hihi === null ? 'NA' : $limit->hihi}}</td>
            </tr>
            @endif
            @if($isEditable)
                <tr>
                    <form wire:submit="save">
                    <td><button type="submit" class="btn btn-primary" wire:click="save" >Update</button></td>
                    <td><input wire:model="lolo" /></td>
                    <td><input wire:model="low" /></td>
                    <td><input wire:model="high"  /></td>
                    <td><input wire:model="hihi"  /></td>
                    </form>
                </tr>
            @endif
        </table>
        @else
            <button type="submit" class="btn btn-primary" wire:click="createLimit({{$meterId}})">Set Thresholds</button>
        @endif

    </div>
</div>
