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
        Schema::create('operative_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('surgeon_id')->constrained('users');
            $table->text('preoperative_diagnosis');
            $table->text('postoperative_diagnosis');
            $table->text('procedure_description');
            $table->text('findings');
            $table->dateTime('procedure_start_time');
            $table->dateTime('procedure_end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operative_notes');
    }
};
