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
        Schema::create('cbc_parameter_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('parameter'); // e.g. 'wbc', 'neutrophils_abs', 'hb', 'mcv'
            $table->string('unit');
            $table->decimal('min', 8, 3)->nullable();
            $table->decimal('max', 8, 3)->nullable();

            // age range in days (store both for precision); use 0 for open
            $table->integer('min_age_days')->default(0);
            $table->integer('max_age_days')->nullable(); // null = no upper bound

            $table->enum('gender', ['male', 'female', 'other', 'any'])->default('any');
            $table->string('label')->nullable(); // e.g. 'Adult male 18-65'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbc_parameter_ranges');
    }
};
