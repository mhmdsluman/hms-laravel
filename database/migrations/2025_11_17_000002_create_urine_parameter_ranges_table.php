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
        Schema::create('urine_parameter_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('parameter'); // e.g., color, specific_gravity, protein
            $table->string('unit')->nullable();
            $table->string('normal_text')->nullable(); // for non-numeric, e.g. "Negative"

            $table->decimal('min', 8, 3)->nullable();
            $table->decimal('max', 8, 3)->nullable();

            $table->integer('min_age_days')->default(0);
            $table->integer('max_age_days')->nullable();
            $table->enum('gender', ['male','female','other','any'])->default('any');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urine_parameter_ranges');
    }
};
