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
        Schema::create('gas_meter_data', function (Blueprint $table) {
            $table->dateTime('date');
            $table->unsignedInteger('meter_id');
            $table->double('ccf')->nullable();
            $table->double('ccfPerMin')->nullable();
            $table->string('src', 20)->default('mya');
            $table->timestamps();

            /*
             * Because we will probably want to partition the table by date
             * or meter we have to ensure those columns are part of the primary key.
             */
            $table->primary(['meter_id', 'date']);

            $table->foreign('meter_id')->references('id')->on('meters');
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
        Schema::dropIfExists('gas_meter_data');
    }
};
