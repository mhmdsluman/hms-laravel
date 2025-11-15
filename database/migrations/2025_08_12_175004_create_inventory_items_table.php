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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->comment('Links to the medication in the service catalogue');
            $table->integer('quantity_in_stock');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('location')->nullable()->comment('e.g., Main Pharmacy, Ward A Cabinet');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
