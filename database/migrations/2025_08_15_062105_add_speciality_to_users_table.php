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
        if (!Schema::hasColumn('users', 'speciality')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('speciality')->nullable()->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'speciality')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('speciality');
            });
        }
    }
};
