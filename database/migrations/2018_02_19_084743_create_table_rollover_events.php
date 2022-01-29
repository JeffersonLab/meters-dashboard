<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRolloverEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rollover_events', function (Blueprint $table) {
            $table->unsignedInteger('meter_id');
            $table->string('field');
            $table->dateTime('rollover_at');
            $table->integer('rollover_accumulated');
            $table->timestamps();

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
        Schema::dropIfExists('rollover_events');
    }
}
