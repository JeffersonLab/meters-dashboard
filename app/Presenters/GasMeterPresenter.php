<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/27/17
 * Time: 10:03 AM
 */

namespace App\Presenters;

class GasMeterPresenter extends MeterPresenter
{
    public function defaultChart()
    {
        return 'dailyccf';
    }

    public function epicsMacroVariables()
    {
        $vars = parent::epicsMacroVariables();
        $vars[] = urlencode(epics_macro_variable('units')).'=ccf';  // macro var passed to screen

        return $vars;
    }
}
