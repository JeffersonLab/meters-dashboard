<?php

namespace App\Models\DataTables;

use App\Models\Meters\Meter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataTableCreator
{

    /**
     * @var Meter
     */
    protected $meter;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var DB
     */
    protected $db;


    public function __construct(Meter $meter){
        $this->meter = $meter;
        $this->schema = Schema::connection(config("database.default"));
        $this->db = DB::connection(config("database.default"));
    }

    /**
     * Create a database table with columns appropriate to the meter type.
     *
     * @return void
     */
    public function createTable(){
        $this->schema->create($this->meter->tableName(), function($table)
        {
            // The columns that are common to all meter types
            $table->increments('id');
            $table->dateTime('date');
            $table->unsignedInteger('meter_id');
            $table->string('src',20)->default('mya');
            $table->timestamps();
            $table->bigInteger('rollover_accumulated')->nullable();
            // The columns that are meter-type specific can be
            // obtained from the meter pv list.  As of now we
            // have only floating point PVs in use.
            foreach ($this->meter->pvFields() as $field) {
                $columnName = substr($field, 1);  //to strip initial ":"
                $table->double($columnName)->nullable();
            }
            // The indexes which are again common across types.
            $table->foreign('meter_id')->references('id')->on('meters');
            $table->index('date');
        });
    }

    /**
     * The pvs to be stored as data columns.
     *
     * @return array|false[]|string[]
     */
    public function pvs(){
        return array_map(fn($value) => substr($value,1), $this->meter->pvFields());
    }

    /**
     * The list of columns for the meter's data tables.
     *
     * @return array|false[]|string[]
     */
    public function columnList(){
        $list = [ 'date', 'meter_id', 'src', 'created_at', 'updated_at', 'rollover_accumulated' ];
        return array_merge($list, $this->pvs());

    }

    /**
     * The name of the data table before conversion to table-per-meter.
     *
     * @return string|void
     */
    public function oldTableName(){
        switch($this->meter->type){
            case 'gas': return 'gas_meter_data';
            case 'power': return 'power_meter_data';
            case 'water': return 'water_meter_data';
        }
    }

    /**
     * Migrate data from the old monolithic single data table into the new per-meter table.
     * @return void
     */
    public function migrateData(){
        $sql = sprintf("insert into %s (%s) select %s from %s where meter_id = %s",
            $this->meter->tableName(),
            implode(',', $this->columnList()),
            implode(',',$this->columnList()),
            $this->oldTableName(),
            $this->meter->id);

        $this->db->statement($sql);
    }

    /**
     * Drop the meter's data table.
     *
     * @return void
     */
    public function dropTable(){
        $this->schema->dropIfExists($this->meter->tableName());
    }


}
