<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\LabTest;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LabInventoryController extends Controller
{
    public function decrementStock(LabTest $labTest)
    {
        $inventoryLinks = json_decode($labTest->inventory_links, true);

        if (!$inventoryLinks) {
            return;
        }

        foreach ($inventoryLinks as $link) {
            $inventoryItem = Inventory::where('name', $link['item_sku'])->first();

            if ($inventoryItem) {
                if ($link['consumption_rule'] === 'per_piece') {
                    $inventoryItem->decrement('quantity', $link['pieces_per_test']);
                }

                if ($inventoryItem->quantity <= $inventoryItem->reorder_level) {
                    $admins = User::where('role', 'admin')->get();
                    Notification::send($admins, new LowStockAlert($inventoryItem));
                }
            }
        }
    }
}
