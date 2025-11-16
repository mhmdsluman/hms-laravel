<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrineTestRequest;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class UrineTestController extends Controller
{
    public function store(StoreUrineTestRequest $request)
    {
        // This controller now delegates to the main LabController
        // to ensure results are stored in the unified LIMS architecture.

        $labController = app(LabController::class);
        $orderItem = OrderItem::findOrFail($request->input('order_item_id'));

        return $labController->storeResult($request, $orderItem);
    }
}
