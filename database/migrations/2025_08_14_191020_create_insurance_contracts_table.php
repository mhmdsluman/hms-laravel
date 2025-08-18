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
        Schema::create('insurance_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_provider_id')->constrained('insurance_providers')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->decimal('coverage_percentage', 5, 2)->comment('e.g., 80.00 for 80% coverage');
            $table->decimal('co_pay_amount', 10, 2)->nullable()->comment('Fixed co-pay amount');
            $table->boolean('requires_pre_authorization')->default(false);
            $table->timestamps();

            $table->unique(['insurance_provider_id', 'service_id']); // Ensure one rule per service per provider
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_contracts');
    }
};
