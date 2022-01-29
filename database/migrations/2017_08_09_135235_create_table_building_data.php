<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBuildingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('building_data', function (Blueprint $table) {
            $table->dateTime('date');
            $table->unsignedInteger('building_id');
            $table->double('gal')->nullable();
            $table->double('totkW')->nullable();
            $table->double('totkWh')->nullable();
            $table->double('totMBTU')->nullable();
            $table->string('src',20)->default('mya');
            $table->timestamps();

            /*
             * Because we will probably want to partition the table by date
             * or building we have to ensure those columns are part of the primary key.
             */
            $table->primary(['building_id','date']);

            $table->foreign('building_id')->references('id')->on('buildings');
            $table->index('date');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_data');
    }
}
