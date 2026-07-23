<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->unsignedBigInteger('affectation_id')->nullable();
            $table->unsignedBigInteger('iddmd')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_notifications');
    }
};
