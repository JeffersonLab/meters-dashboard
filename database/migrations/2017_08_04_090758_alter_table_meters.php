<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMeters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meters', function($table)
        {
            $table->integer('building_id')
                ->unsigned()
                ->nullable();

            $table->foreign('building_id')
                ->references('id')->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meters', function($table){
            $table->dropForeign('meters_building_id_foreign');
            $table->dropColumn('building_id');
        });
    }
}
