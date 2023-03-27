<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('buildings', function ($table) {
            $table->string('type', 255)->nullable();
            $table->unsignedInteger('element_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function ($table) {
            $table->dropColumn('type');
            $table->dropColumn('element_id');
        });
    }
};
