<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_notifications', function (Blueprint $table) {
            $table->string('response')->nullable()->after('is_read');
            $table->timestamp('responded_at')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('service_notifications', function (Blueprint $table) {
            $table->dropColumn(['response', 'responded_at']);
        });
    }
};
