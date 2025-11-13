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
        if (!Schema::hasColumn('services', 'specimen_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('specimen_type')->nullable()->after('department');
            });
        }
        if (!Schema::hasColumn('services', 'units')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('units')->nullable()->after('specimen_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('services', 'specimen_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('specimen_type');
            });
        }
        if (Schema::hasColumn('services', 'units')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('units');
            });
        }
    }
};
