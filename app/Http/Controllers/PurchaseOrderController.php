<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    // List all purchase orders
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'inventoryItems', 'property'])->get();
        return response()->json($purchaseOrders);
    }

    // Show a single purchase order with details
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'inventoryItems', 'property'])->find($id);
        return response()->json($purchaseOrder);
    }

    // Create a new purchase order
    public function store(Request $request)
    {
        $purchaseOrder = PurchaseOrder::create($request->all());
        // Additional logic for linking the order with inventory items and suppliers
        return response()->json($purchaseOrder, 201);
    }

    // Update a purchase order's details
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update($request->all());
        // Additional logic for managing changes in the order, such as status updates
        return response()->json($purchaseOrder, 200);
    }

    // Delete a purchase order
    public function destroy($id)
    {
        PurchaseOrder::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Approving or rejecting a purchase order
    public function approveOrder($orderId, $approvalStatus)
    {
        // Logic for approving or rejecting a purchase order
    }

    // Example: Tracking order delivery
    public function trackDelivery($orderId)
    {
        // Logic for tracking the delivery status of a purchase order
    }
}
