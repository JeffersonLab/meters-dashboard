<div class="row">
    <div class="col-lg-12">
        @include('box.chart',[
            'title' => 'Water Consumption',
            'handle' => $meter->name."-1",
            'chartOptions' => ['dailygallons'=>'Gallons', 'readingsgpm'=>'Flow Rate (GPM)'],
            'chartType' => $meter->defaultChart()
            ])
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        @include('box.meter_data',[
            'title' => 'Meter Data',
            'headings' => ['date'=>'Date','gal' => 'Gallons','galPerMin' => 'GPM','src'=>'Source'],
            'data' => $meter->reporter()->dateRangeQuery()->get(),
            ])
    </div>
</div>