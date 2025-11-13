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
        if (!Schema::hasColumn('clinical_notes', 'provisional_diagnosis_code_id')) {
            Schema::table('clinical_notes', function (Blueprint $table) {
                $table->foreignId('provisional_diagnosis_code_id')->nullable()->after('provisional_diagnosis')->constrained('diagnosis_codes');
            });
        }
        if (!Schema::hasColumn('clinical_notes', 'final_diagnosis_code_id')) {
            Schema::table('clinical_notes', function (Blueprint $table) {
                $table->foreignId('final_diagnosis_code_id')->nullable()->after('final_diagnosis')->constrained('diagnosis_codes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('clinical_notes', 'provisional_diagnosis_code_id')) {
            Schema::table('clinical_notes', function (Blueprint $table) {
                $table->dropForeign(['provisional_diagnosis_code_id']);
                $table->dropColumn('provisional_diagnosis_code_id');
            });
        }
        if (Schema::hasColumn('clinical_notes', 'final_diagnosis_code_id')) {
            Schema::table('clinical_notes', function (Blueprint $table) {
                $table->dropForeign(['final_diagnosis_code_id']);
                $table->dropColumn('final_diagnosis_code_id');
            });
        }
    }
};
