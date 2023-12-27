<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // List all discounts
    public function index()
    {
        $discounts = Discount::with(['reservations', 'guests', 'properties', 'users'])->get();
        return response()->json($discounts);
    }

    // Show a single discount with detailed information
    public function show($id)
    {
        $discount = Discount::with(['reservations', 'guests', 'properties', 'users'])->find($id);
        return response()->json($discount);
    }

    // Create a new discount
    public function store(Request $request)
    {
        $discount = Discount::create($request->all());
        // Additional logic for linking the discount to reservations, guests, or properties
        return response()->json($discount, 201);
    }

    // Update a discount's details
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);
        $discount->update($request->all());
        // Additional logic for managing changes in discount terms or applicability
        return response()->json($discount, 200);
    }

    // Delete a discount
    public function destroy($id)
    {
        Discount::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Applying a discount to a reservation
    public function applyToReservation($discountId, $reservationId)
    {
        // Logic for applying a specific discount to a reservation
    }

    // Example: Activating or deactivating a discount
    public function toggleActivation($discountId)
    {
        // Logic for activating or deactivating a discount
    }
}

