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
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->string('result_value'); // For qualitative or full string results
            $table->decimal('result_numeric', 10, 2)->nullable(); // For numeric values
            $table->string('units')->nullable();
            $table->string('reference_range')->nullable();
            $table->string('flag')->nullable(); // e.g., Normal, High, Low, Critical
            $table->text('notes')->nullable();
            $table->foreignId('entered_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
