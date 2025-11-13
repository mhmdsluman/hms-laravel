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
        if (!Schema::hasColumn('bills', 'insurance_amount')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->decimal('insurance_amount', 10, 2)->default(0.00)->after('total_amount');
                $table->decimal('patient_co_pay', 10, 2)->default(0.00)->after('insurance_amount');
            });
        }

        if (!Schema::hasColumn('bill_items', 'insurance_amount')) {
            Schema::table('bill_items', function (Blueprint $table) {
                $table->decimal('insurance_amount', 10, 2)->default(0.00)->after('total_price');
                $table->decimal('patient_co_pay', 10, 2)->default(0.00)->after('insurance_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bills', 'insurance_amount')) {
            Schema::table('bills', function (Blueprint $table) {
                $table->dropColumn(['insurance_amount', 'patient_co_pay']);
            });
        }

        if (Schema::hasColumn('bill_items', 'insurance_amount')) {
            Schema::table('bill_items', function (Blueprint $table) {
                $table->dropColumn(['insurance_amount', 'patient_co_pay']);
            });
        }
    }
};
