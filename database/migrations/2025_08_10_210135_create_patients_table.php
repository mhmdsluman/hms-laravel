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
        Schema::create('patients', function (Blueprint $table) {
            // Core Identifiers
            $table->id();
            $table->string('uhid')->unique();
            $table->string('mrn')->nullable();
            $table->json('external_ids')->nullable(); // { type, id, issuer }

            // Name & Demographics
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('preferred_name')->nullable();
            $table->string('gender'); // Male, Female, Other, etc.
            $table->string('sex_at_birth')->nullable();
            $table->string('pronouns')->nullable();
            $table->date('date_of_birth');
            $table->string('marital_status')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('nationality')->nullable();
            $table->string('occupation')->nullable();

            // Contact & Address
            $table->string('primary_phone')->unique();
            $table->string('primary_phone_country_code');
            $table->string('secondary_phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->json('addresses')->nullable(); // { type, street, city, etc. }

            // Emergency & Next-of-kin
            $table->json('next_of_kin')->nullable(); // { name, relationship, phone, etc. }

            // Medical Baseline (as JSON for flexibility)
            $table->json('allergies')->nullable();
            $table->json('past_medical_history')->nullable();
            $table->json('surgical_history')->nullable();
            $table->json('family_history')->nullable();
            $table->json('immunization_history')->nullable();

            // Administrative & Legal
            $table->string('patient_portal_password_hash')->nullable();
            $table->json('consent_flags')->nullable(); // { treatment, data_sharing }
            $table->json('legal_flags')->nullable(); // { DNR, organ_donor, etc. }
            $table->string('photo_capture_path')->nullable(); // Path to patient photo

            // Audit & System Fields
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
