<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        Schema::dropIfExists('rollover_events');
    }
};
