<?php

namespace App\Reports;

use App\Models\DataTables\DateRangeTrait;
use App\Models\Meters\Meter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ReportFiltersTrait {

    /**
     * @var Collection
     */
    protected $items;

    /**
     * Names to which the report output should be filtered/limited
     *
     * @var array
     */
    protected $nameFilter = [];



    /**
     * Apply filters from the provided HTTP request.
     *
     * @return $this
     */
    public function applyRequest(Request $request)
    {
        foreach ($request->all() as $filterName => $value) {
            $this->applyNamedFilter($filterName, $value);
        }
        $this->updateItems();

        return $this;
    }

    /**
     * Uses the provided name and value to set up a report filter.
     *
     *
     * @param  string  $filterName
     * @param  string  $value
     */
    public function applyNamedFilter($filterName, $value)
    {
        switch ($filterName) {
            case 'begin':
                $this->beginning($value);
                break;
            case 'end':
                $this->ending($value);
                break;
            case 'pv':
                $this->pv = $value;
                break;
            case 'meters':
                $this->makeNameFilter($value);
        }
    }

    /**
     * Have filters been specified?
     *
     * @return bool
     */
    protected function hasFilters()
    {
        return ! empty($this->nameFilter);
    }

    /**
     * Parses the provided string into an array of meter names and stores it in nameFilter property
     */
    protected function makeNameFilter($string)
    {
        $this->nameFilter = array_filter(preg_split('/[,\r\n]+/', $string));
    }

    /**
     * Update items property with fresh data from the database.
     * For example after applying updated filters.
     *
     * @return void
     */
    protected function updateItems()
    {
        $this->items = Meter::whereIn('epics_name', $this->nameFilter)
            ->with('building')
            ->orderBy('epics_name')->get();
    }

}
