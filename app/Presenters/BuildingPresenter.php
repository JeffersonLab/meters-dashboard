<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/27/17
 * Time: 10:03 AM
 */

namespace App\Presenters;

use App\Charts\DailyKWH;
use Robbo\Presenter\Presenter;
use Collective\Html\HtmlFacade as Html;


class BuildingPresenter extends Presenter implements BoxInterface
{

    function menuLabel()
    {
        $label = (strlen($this->name) > 20) ? $this->abbreviation : $this->name;
        return $this->building_num . ' ' . $label;
    }

    function reportLabel(){
        if ( $this->getAttribute('name_alias') ){
            $label = $this->getAttribute('name_alias');
        }elseif( $this->getAttribute('epics_name') ){
            $label = $this->getAttribute('epics_name');
        }else{
            $label = $this->getAttribute('name');
        }
        return $this->getAttribute('building_num').' '.$label;
    }

    function url(){
        return route('buildings.show', [$this->getAttribute('id')]);
    }

    function linkToEpicsDetailScreen($attributes = ['target' => '_blank'])
    {
        if (isset($this->name)) {
            $var = urlencode(epics_macro_variable('building')) . '=' . $this->name;  // macro var passed to screen
            $screen = env('BASE_SCREEN_URL') . '/building.edl';
            $url = $screen . '&' . $var;
            return link_to($url, 'EPICS Detail Screen', $attributes);
        }
        return null;
    }

    function linkToCedElement($attributes = ['target' => '_blank'])
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Html::linkToCedElement($this->name, 'CED Element Page', $attributes);
    }

    function icon()
    {
        return "<i class=\"fa fa-fw fa-building-o\"></i>";
    }

    /**
     * Returns a label/value array of items to display in box_info.
     *
     * @return array
     */
    function infoBoxItems()
    {
        return [
            'Abbreviation' => $this->abbreviation,
            'Building Number' => $this->building_num,
            'Square Footage' => $this->square_footage,
        ];
    }



    function meterLinks($type=null){
        $meterCollection =  $this->meters()->get();

        if ($type){
            $meterCollection = $meterCollection->where('type','=',$type);
        }
        //dd($meterCollection);
        $links = $meterCollection->map(function (Meter $meter) {
            return link_to_route('meters.show',$meter->getPresenter()->nameWithAlias(), [$meter->id])->__toString();
        });
        return $links;
    }

    /**
     * @return DailyKWH
     */
    public function defaultChart()
    {
        return 'dailykwh';
    }


}
