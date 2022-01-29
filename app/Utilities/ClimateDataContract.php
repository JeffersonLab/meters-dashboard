<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 7/25/17
 * Time: 9:21 AM
 */

namespace App\Utilities;


interface ClimateDataContract
{
    /**
     * Specify a non-default date for which data will be returned.
     *
     * Default should be "yesterday"
     *
     * @param mixed $date
     * @return ClimateDataContract
     */
    function setDate($date);


    /**
     * Retrieve the date of retrieved data set
     *
     * @param mixed $date
     * @return ClimateDataContract
     */
    function getDate();

    /**
     * Returns the name of the data source
     *
     * ex: wunderground, darksky, etc.
     *
     * @return string
     */
    function sourceName();

    /**
     * get Heating Degree Days.
     *
     * @return float
     */
    function heatingDegreeDays();

    /**
     * get Cooling Degree Days.
     *
     * @return float
     */
    function coolingDegreeDays();



}