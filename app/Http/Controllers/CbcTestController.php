<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCbcTestRequest;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CbcTestController extends Controller
{
    public function store(StoreCbcTestRequest $request)
    {
        // This controller now delegates to the main LabController
        // to ensure results are stored in the unified LIMS architecture.

        $labController = app(LabController::class);
        $orderItem = OrderItem::findOrFail($request->input('order_item_id'));

        return $labController->storeResult($request, $orderItem);
    }
}
