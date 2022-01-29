<div class="row">
    <div class="col-lg-12">
        @include('box.chart',[
            'title' => 'Power Consumption',
            'handle' => $meter->name."-1",
            'chartOptions' => [
                'dailykwh'=>'kWh',
                'dailymbtu' => 'MBTU',
                'readingskw'=>'kW',
                'readingsllvolt'=>'Volt'],
            'chartType' => $meter->defaultChart()
            ])
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        @include('box.meter_data',[
            'title' => 'Meter Data',
            'headings' => ['date'=>'Date','totkW'=>'totkW','totkWh'=>'totkWh','totMBTU'=>'totMBTU','llVolt'=>'llVolt', 'src'=>'Source'],
            'data' => $meter->reporter()->dateRangeQuery()->get(),
            ])
    </div>
</div>