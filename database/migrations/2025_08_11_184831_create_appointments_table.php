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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('clinician_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('appointment_time');
            $table->integer('duration_minutes')->default(15);
            $table->string('status')->default('Scheduled'); // e.g., Scheduled, Completed, Cancelled
            $table->text('reason_for_visit')->nullable();
            $table->text('notes')->nullable(); // For clinician notes during the appointment
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
