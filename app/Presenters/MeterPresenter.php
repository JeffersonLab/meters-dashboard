<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/27/17
 * Time: 10:03 AM
 */

namespace App\Presenters;

use Carbon\Carbon;
use Robbo\Presenter\Presenter;
use Collective\Html\HtmlFacade as Html;


abstract class MeterPresenter extends Presenter implements BoxInterface
{

    function linkToEpicsDetailScreen($attributes = ['target' => '_blank'])
    {
        if (isset($this->model_number)) {
            //$var = urlencode(epics_macro_variable('meter')) . '=' . $this->epics_name;  // macro var passed to screen
            $url = env('BASE_SCREEN_URL') . '/' . strtolower($this->model_number) . '.edl';
            if (!empty($this->epicsMacroVariables())) {
                $url .= '&' . implode('&', $this->epicsMacroVariables());
            }
            return link_to($url, 'EPICS Detail Screen', $attributes);
        }
        return null;
    }


    function epicsMacroVariables()
    {
        $vars = array();
        if (isset($this->model_number)) {
            $vars[] = urlencode(epics_macro_variable('meter')) . '=' . $this->epics_name;  // macro var passed to screen
        }
        return $vars;
    }


    function linkToCedElement($attributes = ['target' => '_blank'])
    {
        return Html::linkToCedElement($this->name, 'CED Element Page', $attributes);
    }

    function icon()
    {
        return Html::meterIcon($this->type);
    }

    function nameWithAlias()
    {
        return $this->epics_name;
    }

    function reportLabel(){
        if ( $this->getAttribute('name_alias') ){
            return $this->getAttribute('name_alias');
        }
        if( $this->getAttribute('epics_name') ){
            return $this->getAttribute('epics_name');
        }
        return $this->getAttribute('name');
    }

    function url(){
        return route('meters.show', [$this->getAttribute('id')]);
    }



    function currentStatistics(){
        $fromDate = Carbon::today()->subDays(3);
        $toDate = Carbon::today();
        switch ($this->type){
            case 'power' : return $this->statsBetween('totkW', $fromDate, $toDate);
            case 'water' : return $this->statsBetween('galPerMin', $fromDate, $toDate);
            case 'gas' : return $this->statsBetween('ccfPerMin', $fromDate, $toDate);
        }
        return null;
    }

    /**
     * Returns a label/value array of items to display in a meter
     * info box.
     *
     * @return array
     */
    function infoBoxItems()
    {
        return [
            'Model' => $this->model_number,
            'CED Name' => $this->name,
            'EPICS Name' => $this->epics_name,
            'Description' => $this->name_alias,
            'Location' => link_to_route('buildings.show', $this->housed_by, [$this->building_id]),
            'Date Added' => $this->begins_at->format('Y-m-d'),
            //'First Data' => $this->reporter()->firstData()->get()->date,
        ];
    }


}
