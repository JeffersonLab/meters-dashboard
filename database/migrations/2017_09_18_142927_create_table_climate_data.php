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
    public function up(): void
    {
        Schema::create('climate_data', function (Blueprint $table) {
            $table->dateTime('date');
            $table->float('cooling_degree_days');
            $table->float('heating_degree_days');
            $table->float('degree_days');
            $table->string('src', 20)->default('wunderground');
            $table->timestamps();

            $table->primary('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_data');
    }
};
