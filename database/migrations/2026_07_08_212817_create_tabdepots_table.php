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
        Schema::create('tabdepot', function (Blueprint $table) {
            $table->id();
            $table->string('typdm');
            $table->string('nom');
            $table->string('nni');
            $table->string('tel');
            $table->string('adresse');
            $table->string('daterecp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabdepot');
    }
};
