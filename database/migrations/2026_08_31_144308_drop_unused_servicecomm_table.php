<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Legacy leftover table with no model, controller, or migration referencing
 * it anywhere in the codebase. Confirmed with the project owner that its
 * data is not needed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('servicecomm');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('servicecomm', function (Blueprint $table) {
            $table->id();
            $table->string('usrsv', 100);
            $table->string('sevice', 200);
        });
    }
};
