<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These plural tables were created by mistake alongside the real singular
 * tables actually used by the app's models (tabdepot, affectation,
 * orientation, typedem). They were always empty and unreferenced.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('tabdepots');
        Schema::dropIfExists('affectations');
        Schema::dropIfExists('orientations');
        Schema::dropIfExists('typedems');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tabdepots', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('orientations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('typedems', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
