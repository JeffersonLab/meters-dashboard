<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meters', function ($table) {
            $table->integer('building_id')
                ->unsigned()
                ->nullable();

            $table->foreign('building_id')
                ->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meters', function ($table) {
            $table->dropForeign('meters_building_id_foreign');
            $table->dropColumn('building_id');
        });
    }
};
