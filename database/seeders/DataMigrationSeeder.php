<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Migrate data from inventory_items
        if (DB::schema()->hasTable('inventory_items')) {
            $items = DB::table('inventory_items')->get();
            foreach ($items as $item) {
                DB::table('inventory')->insert([
                    'name' => $item->name,
                    'description' => $item->description,
                    'category' => $item->category,
                    'location' => $item->location,
                    'quantity' => $item->quantity,
                    'reorder_level' => $item->reorder_level,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrate data from lab_inventory_items
        if (DB::schema()->hasTable('lab_inventory_items')) {
            $items = DB::table('lab_inventory_items')->get();
            foreach ($items as $item) {
                DB::table('inventory')->insert([
                    'name' => $item->name,
                    'description' => $item->description,
                    'category' => 'Laboratory', // Assuming all items from this table are in the Laboratory category
                    'location' => $item->location,
                    'quantity' => $item->quantity,
                    'reorder_level' => $item->reorder_level,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
