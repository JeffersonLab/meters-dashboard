<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 8/9/17
 * Time: 2:22 PM
 */

namespace App\Reports;

use Illuminate\Http\Request;

/**
 * Interface ReportInterface
 *
 * Contract that must be implemented by report objects that will be returned
 * by the ReportController.
 */
interface ReportInterface
{
    public function title();

    public function description();

    /*
     * Accept an HTTP Request object and apply its contents to the
     * report.
     *
     * @return void
     */
    public function applyRequest(Request $request);

    /*
     * Return a View object for rendering the report.
     */
    public function view();

    /*
     * Tells if Excel output is available
     *
     * @return bool
     */
    public function hasExcel();

    /*
     * Returns an Excel export object for the report data.
     *
     * @return bool
     */
    public function getExcelExport();

    public function beginsAt();

    public function endsAt();
}
