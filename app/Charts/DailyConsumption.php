<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/1/17
 * Time: 4:17 PM
 */

namespace App\Charts;

use App\Models\DataTables\DataTableInterface;
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
        $result = $this->reporter->dailyPv($this->pv);
        $data = $this->reporter->canvasTimeSeries($result);
        return $data;
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
