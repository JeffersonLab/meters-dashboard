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
     * Create a database table with columns appropriate to our meter type.
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

    public function pvs(){
        return array_map(fn($value) => substr($value,1), $this->meter->pvFields());
    }

    public function columnList(){
        $list = [ 'date', 'meter_id', 'src', 'created_at', 'updated_at', 'rollover_accumulated' ];
        return array_merge($list, $this->pvs());

    }

    public function oldTableName(){
        switch($this->meter->type){
            case 'gas': return 'gas_meter_data';
            case 'power': return 'power_meter_data';
            case 'water': return 'water_meter_data';
        }
    }

    public function migrateData(){
        $sql = sprintf("insert into %s (%s) select %s from %s where meter_id = %s",
            $this->meter->tableName(),
            implode(',', $this->columnList()),
            implode(',',$this->columnList()),
            $this->oldTableName(),
            $this->meter->id);

        $this->db->statement($sql);
    }

    public function dropTable(){
        $this->schema->dropIfExists($this->meter->tableName());
    }


}
