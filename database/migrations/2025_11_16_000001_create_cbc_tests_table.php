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
        Schema::create('cbc_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users');
            $table->date('collected_at')->nullable();
            $table->json('values')->nullable(); // store raw entered numeric values keyed by parameter
            $table->json('calculated')->nullable(); // store auto-calculated values
            $table->json('flags')->nullable(); // simple flagging: low/high for parameters
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbc_tests');
    }
};
