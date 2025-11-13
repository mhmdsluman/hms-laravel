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
        if (!Schema::hasColumn('clinical_notes', 'template_id')) {
            Schema::table('clinical_notes', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->constrained('templates');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('clinical_notes', 'template_id')) {
            Schema::table('clinical_notes', function (Blueprint $table) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            });
        }
    }
};
