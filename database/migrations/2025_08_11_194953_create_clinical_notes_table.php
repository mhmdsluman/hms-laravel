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
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('clinician_id')->constrained('users');
            $table->foreignId('template_id')->nullable()->constrained('templates');

            $table->longText('notes_content')->nullable();

            $table->string('provisional_diagnosis')->nullable();
            $table->foreignId('provisional_diagnosis_code_id')->nullable()->constrained('diagnosis_codes');

            $table->string('final_diagnosis')->nullable();
            $table->foreignId('final_diagnosis_code_id')->nullable()->constrained('diagnosis_codes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
