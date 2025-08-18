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
        Schema::create('reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('gender')->nullable()->comment('e.g., Male, Female, All');
            $table->integer('age_min')->default(0);
            $table->integer('age_max')->default(120);
            $table->decimal('range_low', 10, 2)->nullable();
            $table->decimal('range_high', 10, 2)->nullable();
            $table->decimal('critical_low', 10, 2)->nullable();
            $table->decimal('critical_high', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_ranges');
    }
};
