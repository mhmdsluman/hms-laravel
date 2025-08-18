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
        Schema::create('lab_stock_transactions', function (Blueprint $table) {
            $table->id();
            // foreignId ensures unsignedBigInteger, matching $table->id() above
            $table->foreignId('lab_inventory_item_id')
                  ->constrained('lab_inventory_items')
                  ->onDelete('cascade');

            $table->string('transaction_type')->comment('e.g., Stock In, Usage, Wastage');
            $table->integer('quantity');
            $table->foreignId('user_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_stock_transactions');
    }
};
