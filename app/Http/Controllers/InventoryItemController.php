<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    // List all inventory items
    public function index()
    {
        $items = InventoryItem::with(['property', 'suppliers'])->get();
        return response()->json($items);
    }

    // Show a single inventory item with details
    public function show($id)
    {
        $item = InventoryItem::with(['property', 'suppliers'])->find($id);
        return response()->json($item);
    }

    // Add a new inventory item
    public function store(Request $request)
    {
        $item = InventoryItem::create($request->all());
        // Additional logic for setting up inventory tracking, linking to suppliers, etc.
        return response()->json($item, 201);
    }

    // Update an inventory item's details
    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->update($request->all());
        // Additional logic for updating inventory status or supplier details
        return response()->json($item, 200);
    }

    // Delete an inventory item
    public function destroy($id)
    {
        InventoryItem::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Checking inventory levels
    public function checkInventoryLevels()
    {
        // Logic to check and report on current inventory levels
    }

    // Example: Reordering items from suppliers
    public function reorderItem($itemId)
    {
        // Logic for initiating a reorder of an inventory item
    }
}
