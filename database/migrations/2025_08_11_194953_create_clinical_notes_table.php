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
            // template_id is added in a later migration so we avoid creating a foreign key
            // here that references a table which may not yet exist when migrations run.
            // The migration `2025_08_12_223328_add_template_id_to_clinical_notes_table.php`
            // will add the `template_id` column and constraint after `templates` exists.

            $table->longText('notes_content')->nullable();

            $table->string('provisional_diagnosis')->nullable();
            // provisional_diagnosis_code_id is added in a later migration so we avoid
            // creating the FK here which would reference a table created later.

            $table->string('final_diagnosis')->nullable();
            // final_diagnosis_code_id is added in a later migration so we avoid
            // creating the FK here which would reference a table created later.

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
