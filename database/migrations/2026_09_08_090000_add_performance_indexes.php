<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->index('statut_circuit');
            $table->index('service_assigne');
            $table->index('decision_maire');
            $table->index('daterecp');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('service');
        });

        Schema::table('service_notifications', function (Blueprint $table) {
            $table->index('service');
        });
    }

    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropIndex(['statut_circuit']);
            $table->dropIndex(['service_assigne']);
            $table->dropIndex(['decision_maire']);
            $table->dropIndex(['daterecp']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['service']);
        });

        Schema::table('service_notifications', function (Blueprint $table) {
            $table->dropIndex(['service']);
        });
    }
};
