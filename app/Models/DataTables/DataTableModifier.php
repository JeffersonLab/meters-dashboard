<?php

namespace App\Models\DataTables;

use App\Exceptions\DataConversionException;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataTableModifier
{
    /**
     * @var Meter|Building
     */
    protected $model;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var DB
     */
    protected $db;

    /**
     * DataTableModifier constructor.
     */
    public function __construct(DataTableInterface $model)
    {
        $this->model = $model;
        $this->schema = Schema::connection(config('database.default'));
        $this->db = DB::connection(config('database.default'));
    }

    /**
     * Assert that a table exists before attempting to modify it.
     *
     *
     * @throws DataConversionException
     */
    protected function assertTableExists($table)
    {
        if (! $this->schema->hasTable($table)) {
            throw new DataConversionException("Table $table does not exist");
        }
    }

    /**
     * Add gas meter data columns to an existing building data table.
     *
     * @throws DataConversionException
     */
    public function addGasMeterColumns()
    {
        $this->assertTableExists($this->model->tableName());
        $this->addMeterColumns($this->model->tableName(), 'gas');
    }

    /**
     * Add water meter data columns to an existing building data table.
     *
     * @throws DataConversionException
     */
    public function addWaterMeterColumns()
    {
        $this->assertTableExists($this->model->tableName());
        $this->addMeterColumns($this->model->tableName(), 'water');
    }

    /**
     * Add power meter data columns to an existing building data table.
     *
     * @throws DataConversionException
     */
    public function addPowerMeterColumns()
    {
        $this->assertTableExists($this->model->tableName());
        $this->addMeterColumns($this->model->tableName(), 'power');
    }

    /**
     * Add PV columns to a building's data table after it has already been created.
     * For examples if gas meters were added to a building that did not previously have them.
     *
     * @param  string  $type  (gas, power, water)
     */
    protected function addMeterColumns(string $tableName, string $type)
    {
        // TODO strip out unnecessary columns?
        $this->schema->table($tableName, function ($table) use ($type) {
            foreach (array_keys(config('meters.pvs.'.$type)) as $field) {
                $columnName = substr($field, 1);  //to strip initial ":"
                $table->double($columnName)->nullable();
            }
        });
    }
}
