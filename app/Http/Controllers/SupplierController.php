<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // List all suppliers
    public function index()
    {
        $suppliers = Supplier::with(['inventoryItems', 'purchaseOrders', 'properties'])->get();
        return response()->json($suppliers);
    }

    // Show a single supplier with detailed information
    public function show($id)
    {
        $supplier = Supplier::with(['inventoryItems', 'purchaseOrders', 'properties'])->find($id);
        return response()->json($supplier);
    }

    // Add a new supplier
    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());
        // Additional logic for setting up supplier relationships, such as linking to inventory items
        return response()->json($supplier, 201);
    }

    // Update a supplier's details
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        // Additional logic for managing supplier information and orders
        return response()->json($supplier, 200);
    }

    // Delete a supplier
    public function destroy($id)
    {
        Supplier::find($id)->delete();
        // Additional cleanup logic if required, like adjusting inventory item records
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Managing supplier orders
    public function manageOrders($supplierId, Request $request)
    {
        // Logic for managing orders placed with the supplier
    }
}
