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
        Schema::create('care_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_plan_id')->constrained('care_plans')->onDelete('cascade');
            $table->text('nursing_diagnosis');
            $table->text('goals');
            $table->text('interventions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_plan_items');
    }
};
