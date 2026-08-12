<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->enum('origine', ['interne', 'externe'])->nullable()->after('typdm');
        });
    }

    public function down(): void
    {
        Schema::table('tabdepot', function (Blueprint $table) {
            $table->dropColumn('origine');
        });
    }
};
