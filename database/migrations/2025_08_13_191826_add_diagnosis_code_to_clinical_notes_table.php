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
        Schema::table('clinical_notes', function (Blueprint $table) {
            $table->foreignId('provisional_diagnosis_code_id')->nullable()->after('provisional_diagnosis')->constrained('diagnosis_codes');
            $table->foreignId('final_diagnosis_code_id')->nullable()->after('final_diagnosis')->constrained('diagnosis_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinical_notes', function (Blueprint $table) {
            $table->dropForeign(['provisional_diagnosis_code_id']);
            $table->dropForeign(['final_diagnosis_code_id']);
            $table->dropColumn(['provisional_diagnosis_code_id', 'final_diagnosis_code_id']);
        });
    }
};
