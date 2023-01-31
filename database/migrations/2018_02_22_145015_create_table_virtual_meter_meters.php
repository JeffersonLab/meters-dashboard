<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_meter_meters', function (Blueprint $table) {
            $table->unsignedInteger('virtual_meter_id');
            $table->unsignedInteger('meter_id');
            $table->timestamps();

            $table->primary(['virtual_meter_id', 'meter_id']);

            $table->foreign('virtual_meter_id')
                ->references('id')->on('virtual_meters')
                ->onDelete('cascade');

            $table->foreign('meter_id')
                ->references('id')->on('meters')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('virtual_meter_meters');
    }
};
