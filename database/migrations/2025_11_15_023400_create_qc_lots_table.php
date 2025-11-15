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
        Schema::create('qc_lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number')->unique();
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            $table->float('mean');
            $table->float('std_dev');
            $table->date('expiry_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_lots');
    }
};
