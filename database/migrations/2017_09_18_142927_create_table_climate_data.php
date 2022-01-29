<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableClimateData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('climate_data', function (Blueprint $table) {
            $table->dateTime('date');
            $table->float('cooling_degree_days');
            $table->float('heating_degree_days');
            $table->float('degree_days');
            $table->string('src',20)->default('wunderground');
            $table->timestamps();

            $table->primary('date');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('climate_data');
    }
}
