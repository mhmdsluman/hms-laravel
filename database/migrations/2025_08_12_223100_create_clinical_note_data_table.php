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
        Schema::create('clinical_note_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinical_note_id')->constrained('clinical_notes')->onDelete('cascade');
            $table->foreignId('template_field_id')->constrained('template_fields')->onDelete('cascade');
            $table->text('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_note_data');
    }
};
