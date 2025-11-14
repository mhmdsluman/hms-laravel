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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('code')->unique()->nullable();
            $table->string('category')->nullable();
            $table->string('department')->nullable();
            $table->string('units')->nullable();
            $table->integer('estimated_time')->nullable()->comment('in minutes');
            $table->json('reference_ranges')->nullable();
            $table->boolean('normal_for_age_display')->default(false);
            $table->string('print_highlight')->default('red_if_outside');
            $table->json('inventory_links')->nullable();
            $table->timestamps();
        });

        Schema::create('panel_components', function (Blueprint $table) {
            $table->id();
            $table->string('panel_code');
            $table->string('component_code');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('lab_order_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->onDelete('cascade');
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            $table->string('result')->nullable();
            $table->boolean('is_abnormal')->default(false);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_order_results');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('panel_components');
        Schema::dropIfExists('lab_tests');
    }
};
