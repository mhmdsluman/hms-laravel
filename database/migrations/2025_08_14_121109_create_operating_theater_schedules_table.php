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
        Schema::create('operating_theater_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time');
            $table->string('theater_room'); // e.g., 'OT-1', 'OT-2'
            $table->foreignId('surgeon_id')->constrained('users');
            $table->foreignId('anesthetist_id')->nullable()->constrained('users');
            $table->foreignId('scrub_nurse_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_theater_schedules');
    }
};
