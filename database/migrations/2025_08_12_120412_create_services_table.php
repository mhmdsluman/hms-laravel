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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('department'); // e.g., 'Laboratory', 'Radiology', 'Pharmacy'
            $table->string('specimen_type')->nullable();
            $table->string('units')->nullable();
            $table->string('formulary_status')->nullable()->comment('e.g., Formulary, Non-Formulary, Restricted');
            $table->boolean('is_controlled_substance')->default(false);
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
