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
        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_id')->constrained('admissions')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->comment('The specific medication order');
            $table->dateTime('scheduled_time');
            $table->dateTime('administered_time')->nullable();
            $table->foreignId('administered_by_user_id')->nullable()->constrained('users')->comment('The nurse who administered');
            $table->string('status')->default('Due'); // e.g., Due, Administered, Refused, Delayed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_administrations');
    }
};
