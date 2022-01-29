<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBuildingDataAddCcf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('building_data', function($table){
            $table->double('ccf')->nullable();
        });
        Schema::table('building_data', function($table){
            $table->double('ccfPerMin')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('building_data', function($table){
            $table->dropColumn('ccf');
        });
        Schema::table('building_data', function($table){
            $table->dropColumn('ccfPerMin');
        });
    }
}
