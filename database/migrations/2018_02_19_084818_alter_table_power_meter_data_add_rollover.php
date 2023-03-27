<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('power_meter_data', function ($table) {
            $table->integer('rollover_accumulated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('power_meter_data', function ($table) {
            $table->dropColumn('rollover_accumulated');
        });
    }
};
