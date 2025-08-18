1. Update the Database
This is the code for the new migration file that adds discount fields to the bills table.

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
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('patient_co_pay');
            $table->string('discount_reason')->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_reason']);
        });
    }
};
