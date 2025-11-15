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
        Schema::create('qc_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_lot_id')->constrained('qc_lots')->onDelete('cascade');
            $table->float('result');
            $table->boolean('in_range');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_results');
    }
};
