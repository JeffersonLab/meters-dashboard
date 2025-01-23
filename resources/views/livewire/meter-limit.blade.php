<div class="card meter-limits">
    <div class="card-header">

        <h4 class="mb-0">
            <i class="fa fa-bell text-orange"></i>
            Consumption Alert Thresholds
        @if(Auth::user())
            @if($limit)
                <i class="fa fa-edit text-gray" style="float:right;"
                   wire:click.prevent="toggleEdit" title="Edit threshold values" data-toggle="tooltip"></i>
            @endif
        @endif
        </h4>

    </div>
    <div class="card-body">
        @if ($limit || $enableEdit)
            <table class="table">
                <tr>
                    <th>Units</th>
                    <th title="too low critical" data-toggle="tooltip">LoLo</th>
                    <th title="too low warning" data-toggle="tooltip">Low</th>
                    <th title="too high warning" data-toggle="tooltip">High</th>
                    <th title="too high critical" data-toggle="tooltip">HiHi</th>
                </tr>
                @if ($limit)
                    <tr>
                        <td>{{$label}}</td>
                        <td>{{$limit->lolo === NULL ? 'NA' : $limit->lolo}}</td>
                        <td>{{$limit->low === NULL ? 'NA' : $limit->low}}</td>
                        <td>{{$limit->high === NULL ? 'NA' : $limit->high}}</td>
                        <td>{{$limit->hihi === NULL ? 'NA' : $limit->hihi}}</td>
                    </tr>
                @endif
                @if($enableEdit)
                    <tr>
                        <form wire:submit="save">
                            <td>
                                <button type="submit" class="btn btn-primary"
                                        wire:click="save">Update
                                </button>
                            </td>
                            <td><input wire:model="lolo"/></td>
                            <td><input wire:model="low"/></td>
                            <td><input wire:model="high"/></td>
                            <td><input wire:model="hihi"/></td>
                        </form>
                    </tr>
                    @if ($errors->any())
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif

                @endif
            </table>
        @else
            <button type="submit" class="btn btn-primary"
                    wire:click="createLimit({{$meterId}})">Set Thresholds
            </button>
        @endif

    </div>
</div>
