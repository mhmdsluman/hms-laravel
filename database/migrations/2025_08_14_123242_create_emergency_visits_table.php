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
        Schema::create('emergency_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->timestamp('arrival_time');
            $table->string('chief_complaint');
            $table->string('triage_level')->nullable(); // e.g., ESI 1-5, Red/Yellow/Green
            $table->string('status')->default('Waiting'); // e.g., Waiting, In-Progress, Discharged, Admitted
            $table->foreignId('registered_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_visits');
    }
};
