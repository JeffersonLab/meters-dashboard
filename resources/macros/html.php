<?php

/**
 * Register macro and content extensions of the laravelcollective HtmlBuilder.
 */
Html::macro('linkToCedElement', function ($name, $title, $params = []) {
    $url = env('CED_URL', 'https://ced.acc.jlab.org/').'elem/'.$name;

    return link_to($url, $title, $params);
});

/**
 * Generate HTML for icon with correct color and symbol for the
 * specified meter type.
 */
Html::macro('meterIcon', function ($meterType) {
    switch ($meterType) {
        case 'power':  $color = config('meters.icons.power.color');
            $symbol = config('meters.icons.power.symbol');
            break;
        case 'water':  $color = config('meters.icons.water.color');
            $symbol = config('meters.icons.water.symbol');
            break;
        case 'gas':  $color = config('meters.icons.gas.color');
            $symbol = config('meters.icons.gas.symbol');
            break;
        case 'cooling-tower':  $color = config('meters.icons.cooling_tower.color');
            $symbol = config('meters.icons.cooling_tower.symbol');
            break;
    }
    if ($color && $symbol) {
        return sprintf('<i class="fa fa-fw fa-%s text-%s"></i>', $symbol, $color);
    }

    return '';
});
