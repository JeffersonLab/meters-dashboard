<?php

namespace App\Models\DataTables;

use App\Exceptions\DataConversionException;
use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataTableCreator
{
    /**
     * @var Meter
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

    public function __construct(DataTableInterface $model)
    {
        $this->model = $model;
        $this->schema = Schema::connection(config('database.default'));
        $this->db = DB::connection(config('database.default'));
    }

    protected function assertTableDoesNotExist($table)
    {
        if ($this->schema->hasTable($table)) {
            throw new DataConversionException("Table $table already exists");
        }
    }

    /**
     * Create a database table with columns appropriate to the meter type.
     *
     * @return void
     */
    public function createTable()
    {
        $this->assertTableDoesNotExist($this->model->tableName());
        $this->schema->create($this->model->tableName(), function ($table) {
            // The columns that are common to all meter types
            $table->increments('id');
            $table->dateTime('date');
            $table->unsignedInteger($this->fk());
            $table->string('src', 20)->default('mya');
            $table->timestamps();
            $table->bigInteger('rollover_accumulated')->nullable();
            // The columns that are meter-type specific can be
            // obtained from the meter pv list.  As of now we
            // have only floating point PVs in use.
            foreach ($this->model->dbFields() as $field) {
                $columnName = substr($field, 1);  //to strip initial ":"
                $table->double($columnName)->nullable();
            }
            // The index construction differs for buildings vs meters
            if ($this->model instanceof Building) {
                $table->foreign($this->fk())->references('id')->on('buildings')->onDelete('cascade');
            } else {
                $table->foreign($this->fk())->references('id')->on('meters')->onDelete('cascade');
            }
            $table->index('date');
        });
    }

    /**
     * The pvs to be stored as data columns.
     *
     * @return array|false[]|string[]
     */
    public function pvs()
    {
        return array_map(fn ($value) => substr($value, 1), $this->model->pvFields());
    }

    /**
     * The foreign key column name in the data table.
     * It will be meter_id in a meter_data_* table and building_id in a building_data_table
     */
    protected function fk(): string
    {
        if ($this->model instanceof Building) {
            return 'building_id';
        }

        return 'meter_id';
    }

    /**
     * The list of columns for the meter's data tables.
     *
     * @return array|false[]|string[]
     */
    public function columnList()
    {
        if ($this->model instanceof Building) {
            $list = ['date', $this->fk(), 'src', 'created_at', 'updated_at'];
        } else {
            $list = ['date', $this->fk(), 'src', 'created_at', 'updated_at', 'rollover_accumulated'];
        }

        return array_merge($list, $this->pvs());
    }

    /**
     * The name of the data table before conversion to table-per-meter/building.
     *
     * @return string|null
     */
    public function oldTableName()
    {
        if ($this->model instanceof Building) {
            return 'building_data';
        }
        if ($this->model instanceof Meter) {
            switch ($this->model->type) {
                case 'gas':
                    return 'gas_meter_data';
                case 'power':
                    return 'power_meter_data';
                case 'water':
                    return 'water_meter_data';
            }
        }

        return null;
    }

    /**
     * Migrate data from the old monolithic single data table into the new per-meter table.
     *
     * @return void
     */
    public function migrateData()
    {
        $sql = sprintf('insert into %s (%s) select %s from %s where %s = %s',
            $this->model->tableName(),
            implode(',', $this->columnList()),
            implode(',', $this->columnList()),
            $this->oldTableName(),
            $this->fk(),
            $this->model->id);

        $this->db->statement($sql);
    }

    /**
     * Drop the meter's data table.
     *
     * @return void
     */
    public function dropTable()
    {
        $this->schema->dropIfExists($this->model->tableName());
    }
}
