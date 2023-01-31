<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterTableGasMeterDataAddRollover extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gas_meter_data', function ($table) {
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
        Schema::table('gas_meter_data', function ($table) {
            $table->dropColumn('rollover_accumulated');
        });
    }
}
