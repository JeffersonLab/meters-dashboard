<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/17/17
 * Time: 10:19 AM
 */

namespace App\Charts;


use Illuminate\Http\Request;


interface ChartInterface
{

    /**
     * Accepts and applies parameters from an HTTP request.
     *
     * @param Request $request
     * @return mixed
     */
    function applyRequest(Request $request);

    /**
     * Returns the collection of data points to be plotted.
     *
     * @return \Illuminate\Support\Collection
     */
    function chartData();

    /**
     * Returns an array representation of chart settings and data.
     *
     * @return array
     */
    function toArray();

    /**
     * Returns an JSON string representation of chart settings and data.
     *
     * @return string
     */
    function toJson();

}