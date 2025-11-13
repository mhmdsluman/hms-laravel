<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\LabInventoryItem;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Migrate data from the old inventory_items table
        $inventoryItems = InventoryItem::all();
        foreach ($inventoryItems as $item) {
            Inventory::create([
                'name' => $item->name,
                'description' => $item->description,
                'category' => 'Pharmacy',
                'location' => $item->location,
                'quantity' => $item->quantity,
                'reorder_level' => $item->reorder_level,
            ]);
        }

        // Migrate data from the old lab_inventory_items table
        $labInventoryItems = LabInventoryItem::all();
        foreach ($labInventoryItems as $item) {
            Inventory::create([
                'name' => $item->name,
                'description' => $item->description,
                'category' => 'Lab',
                'location' => $item->location,
                'quantity' => $item->quantity,
                'reorder_level' => $item->reorder_level,
            ]);
        }
    }
}
