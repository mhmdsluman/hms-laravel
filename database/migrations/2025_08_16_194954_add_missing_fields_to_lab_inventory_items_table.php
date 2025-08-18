<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_inventory_items', function (Blueprint $table) {
            // add new columns (nullable/defaults to avoid breaking existing inserts)
            if (!Schema::hasColumn('lab_inventory_items', 'item_type')) {
                $table->string('item_type')->nullable()->after('sku');
            }
            if (!Schema::hasColumn('lab_inventory_items', 'quantity_in_stock')) {
                $table->integer('quantity_in_stock')->nullable()->after('description');
            }
            if (!Schema::hasColumn('lab_inventory_items', 'reorder_level')) {
                $table->integer('reorder_level')->nullable()->after('quantity_in_stock');
            }
            if (!Schema::hasColumn('lab_inventory_items', 'supplier')) {
                $table->string('supplier')->nullable()->after('reorder_level');
            }
        });

        // Copy existing 'quantity' values into new 'quantity_in_stock' if present
        // and set defaults where appropriate.
        if (Schema::hasColumn('lab_inventory_items', 'quantity') && Schema::hasColumn('lab_inventory_items', 'quantity_in_stock')) {
            DB::statement('UPDATE lab_inventory_items SET quantity_in_stock = COALESCE(quantity_in_stock, quantity)');
        }

        // Optional: set non-null defaults after migrating values (uncomment if desired)
        // Schema::table('lab_inventory_items', function (Blueprint $table) {
        //     $table->integer('quantity_in_stock')->default(0)->change();
        //     $table->integer('reorder_level')->default(0)->change();
        // });
    }

    public function down(): void
    {
        Schema::table('lab_inventory_items', function (Blueprint $table) {
            // drop the columns we added if they exist
            if (Schema::hasColumn('lab_inventory_items', 'item_type')) {
                $table->dropColumn('item_type');
            }
            if (Schema::hasColumn('lab_inventory_items', 'quantity_in_stock')) {
                $table->dropColumn('quantity_in_stock');
            }
            if (Schema::hasColumn('lab_inventory_items', 'reorder_level')) {
                $table->dropColumn('reorder_level');
            }
            if (Schema::hasColumn('lab_inventory_items', 'supplier')) {
                $table->dropColumn('supplier');
            }
        });
    }
};
