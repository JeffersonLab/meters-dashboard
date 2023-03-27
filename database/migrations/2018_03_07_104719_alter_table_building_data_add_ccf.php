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
        Schema::table('building_data', function ($table) {
            $table->double('ccf')->nullable();
        });
        Schema::table('building_data', function ($table) {
            $table->double('ccfPerMin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('building_data', function ($table) {
            $table->dropColumn('ccf');
        });
        Schema::table('building_data', function ($table) {
            $table->dropColumn('ccfPerMin');
        });
    }
};
