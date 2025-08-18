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
        Schema::create('anesthesia_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operative_note_id')->constrained('operative_notes')->onDelete('cascade');
            $table->foreignId('anesthetist_id')->constrained('users');
            $table->string('anesthesia_type'); // e.g., General, Spinal, Epidural
            $table->json('vitals_log')->nullable()->comment('Timestamped log of vitals during procedure');
            $table->json('medications_log')->nullable()->comment('Timestamped log of medications administered');
            $table->json('fluids_log')->nullable()->comment('Timestamped log of fluids administered');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anesthesia_records');
    }
};
