<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80)->unique();
            $table->string('abbreviation', 20)->nullable();
            $table->string('building_num', 20)->nullable();
            $table->string('jlab_name', 80)->nullable();
            $table->float('square_footage')->nullable();
            $table->dateTime('begins_at')->default(Carbon::now());
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
