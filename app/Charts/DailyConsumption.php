<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/1/17
 * Time: 4:17 PM
 */

namespace App\Charts;

use App\Models\DataTables\DataTableInterface;
use App\Models\Meters\Meter;
use Illuminate\Http\Request;

/**
 * Class DailyConsumption
 *
 * Plot units consumption (gal, kwH, ccf) on a daily basis.
 *
 * @package App\Charts
 */

class DailyConsumption implements ChartInterface
{

    protected $model;
    public $reporter;
    public $type = 'column';
    public $pv;
    public $title;


    /**
     * DailyConsumption constructor.
     *
     * @param DataTableInterface $model
     * @param string $pv
     * @param string $title (optional)
     */
    public function __construct(DataTableInterface $model, $pv, $title = null)
    {
        $this->model = $model;
        $this->reporter = $model->reporter();
        $this->pv = $pv;
        if ($title){
            $this->title = $title;
        }else{
            $this->title = 'Daily '.$pv;
        }
    }

    function applyRequest(Request $request)
    {
        $this->setDateRange($request->input('start'), $request->input('end', null));
    }

    function setDateRange($start, $end){
        $this->reporter->beginning($start);
        if ($end){
            $this->reporter->ending($end);
        }else{
            $this->reporter->defaultEnding();
        }
    }



    /**
     * Returns the collection of data points to be plotted.
     *
     * @return \Illuminate\Support\Collection
     */
    public function chartData(){
        if (is_a($this->model, Meter::class)){
            $query = $this->model->dailyConsumptionQuery($this->pv, $this->reporter->begins_at, $this->reporter->ends_at);
            // Must recast the query output column names into a new collection with
            // keys generically named "label" and "value"
            $result = $query->get()->map(function($item){
                return (object) [
                    'label' => $item->date,
                    'value' => $item->consumed,
                ];
            });
        }else{
            $result = $this->reporter->dailyPv($this->pv);
        }
        return $this->reporter->canvasTimeSeries($result);
    }

    /**
     * Returns an array representation of chart settings and data.
     *
     * @return array
     */
    public function toArray(){
        return [
            'title' => [
                'text' => $this->title,
            ],
            'data' => [
                [
                    'color' => '#B0D0B0',
                    'type' => $this->type,
                    'xValueType' => 'dateTime',
                    'dataPoints' => $this->chartData()->toArray(),
                ],
            ],
        ];
    }

    /**
     * Returns an JSON string representation of chart settings and data.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());

    }

}
