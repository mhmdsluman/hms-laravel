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
        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->string('accession_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('lab_order_id')->constrained('lab_orders')->onDelete('cascade');
            $table->string('sample_type'); // E.g., 'Serum', 'Plasma', 'Urine', 'Tissue'
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users');
            $table->string('status'); // E.g., 'Awaiting Collection', 'Collected', 'In-Progress', 'Stored'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_samples');
    }
};
