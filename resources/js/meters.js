/**
 * Namespace
 * @type {{}}
 */
var jlab = jlab || {};
jlab.meters = jlab.meters || {};
jlab.meters.chart = jlab.meters.chart || {};



/**
 * Meters graphing via chartjs
 */
jlab.meters.makeChart = function(){
    console.log('howdy from makeChart ');
    var chartId = $(this).attr('id');
    var chartType = $(this).data('type');
    jlab.meters.chart[chartId] = new CanvasJS.Chart(chartId);
    console.log( jlab.meters.chart[chartId] );
    jlab.meters.getChartOptions(chartType, chartId);
};

jlab.meters.changeChart = function (){
    jlab.meters.getChartOptions($(this).val(), $(this).data('chart'));
};

jlab.meters.getChartOptions = function(chartType, chartId) {
    console.log(chartId);
    console.log(jlab.currentApiUrl);
    $.get(jlab.currentApiUrl, {
            'start' : jlab.currentDateRange.begins,
            'end'   : jlab.currentDateRange.ends,
            'chart' : chartType,
            'model_id' : jlab.currentModel.id
        },
        function(response){
            console.log(response.data);
            console.log('zz',jlab.meters.chart);
            if (response.status == 'ok') {
                jlab.meters.chart[chartId].options = response.data;
                jlab.meters.chart[chartId].render();
            }else{
                alert(response);
            }
        }
    ).fail(function(jqxhr) {
        if (jqxhr.responseJSON){
            alert( jqxhr.responseJSON );
        }else{
            console.log(jqxhr);
            alert('unable to obtain chart');
        }
    });
};
