<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/27/17
 * Time: 10:03 AM
 */

namespace App\Presenters;

class WaterMeterPresenter extends MeterPresenter
{
    /**
     * @return string
     */
    public function defaultChart(): string
    {
        return 'dailygallons';
    }

    public function epicsMacroVariables()
    {
        $vars = parent::epicsMacroVariables();
        $vars[] = urlencode(epics_macro_variable('units')).'=gal';  // macro var passed to screen

        return $vars;
    }
}
