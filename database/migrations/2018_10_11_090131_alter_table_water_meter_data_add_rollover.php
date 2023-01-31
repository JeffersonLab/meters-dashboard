<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterTableWaterMeterDataAddRollover extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('water_meter_data', function ($table) {
            $table->integer('rollover_accumulated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('water_meter_data', function ($table) {
            $table->dropColumn('rollover_accumulated');
        });
    }
}
