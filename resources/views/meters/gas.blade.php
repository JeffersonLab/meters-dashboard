<div class="row">
    <div class="col-lg-12">
        @include('box.chart',[
            'title' => 'Gas Consumption',
            'handle' => $meter->name."-1",
            'chartOptions' => ['dailyccf'=>'CCF', 'readingsgpm'=>'Flow Rate (CCF)'],
            'chartType' => $meter->defaultChart()
            ])
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        @include('box.meter_data',[
            'title' => 'Meter Data',
            'headings' => ['date'=>'Date','ccf' => 'CCF','ccfPerMin' => 'CCFPM','src'=>'Source'],
            'data' => $meter->reporter()->dateRangeQuery()->get(),
            ])
    </div>
</div>