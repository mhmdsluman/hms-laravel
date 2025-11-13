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
        if (!Schema::hasColumn('services', 'formulary_status')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('formulary_status')->nullable()->after('units')->comment('e.g., Formulary, Non-Formulary, Restricted');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('services', 'formulary_status')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('formulary_status');
            });
        }
    }
};
