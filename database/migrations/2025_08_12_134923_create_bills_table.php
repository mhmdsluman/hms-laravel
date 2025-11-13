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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('insurance_amount', 10, 2)->default(0.00);
            $table->decimal('patient_co_pay', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('discount_reason')->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->string('status'); // e.g., 'Draft', 'Unpaid', 'Paid', 'Void'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
