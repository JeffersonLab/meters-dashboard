<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterTablePowerMeterDataAddLlvolt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('power_meter_data', function ($table) {
            $table->double('llVolt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('power_meter_data', function ($table) {
            $table->dropColumn('llVolt');
        });
    }
}
