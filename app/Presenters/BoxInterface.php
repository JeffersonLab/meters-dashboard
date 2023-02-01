<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/9/17
 * Time: 2:22 PM
 */

namespace App\Presenters;

/**
 * Interface BoxInterface
 *
 * Contract that must be implemented by objects that will be used with the
 * partials.box_* views.
 */
interface BoxInterface
{
    /**
     * Returns a label/value array of items to display in a meter
     * info box.
     *
     * @return array
     */
    public function infoBoxItems();

    public function linkToEpicsDetailScreen();

    public function linkToCedElement();

    public function defaultChart();
}
