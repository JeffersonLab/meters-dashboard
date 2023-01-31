<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMeterLimits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meter_limits', function (Blueprint $table) {
            $table->unsignedInteger('meter_id');
            $table->string('field', 32);
            $table->integer('interval');
            $table->double('low', 8, 2)->nullable();
            $table->double('high', 8, 2)->nullable();
            $table->double('lolo', 8, 2)->nullable();
            $table->double('hihi', 8, 2)->nullable();
            $table->string('source')->default('epics');
            $table->timestamps();

            $table->unique(['meter_id', 'field']);

            $table->foreign('meter_id')->references('id')->on('meters')
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
        Schema::dropIfExists('meter_limits');
    }
}
