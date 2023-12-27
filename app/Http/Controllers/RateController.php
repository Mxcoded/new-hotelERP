<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    // List all rates
    public function index()
    {
        $rates = Rate::with(['roomType', 'property'])->get();
        return response()->json($rates);
    }

    // Show a single rate with detailed information
    public function show($id)
    {
        $rate = Rate::with(['roomType', 'property'])->find($id);
        return response()->json($rate);
    }

    // Create a new rate
    public function store(Request $request)
    {
        // Validate request, ensure associated room type and property are valid
        $rate = Rate::create($request->all());
        // Additional logic for setting up the rate (e.g., linking to promotions)
        return response()->json($rate, 201);
    }

    // Update a rate's details
    public function update(Request $request, $id)
    {
        $rate = Rate::findOrFail($id);
        $rate->update($request->all());
        // Additional logic for updating rate details or associated information
        return response()->json($rate, 200);
    }

    // Delete a rate
    public function destroy($id)
    {
        Rate::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Adjusting rates for specific dates or events
    public function adjustForDates(Request $request)
    {
        // Logic for temporary rate adjustments
    }

    // Example: Linking rates with promotions
    public function linkPromotion($rateId, $promotionId)
    {
        // Logic to link a rate with a promotion
    }
}

