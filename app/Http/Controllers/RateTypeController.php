<?php

namespace App\Http\Controllers;

use App\Models\RateType;
use Illuminate\Http\Request;

class RateTypeController extends Controller
{
    // List all rate types
    public function index()
    {
        // // Check if a property_id is provided
        // $propertyId = $request->query('property_id');

        $rateTypes = RateType::with(['rates', 'promotions', 'properties'])->get();
        return response()->json($rateTypes);
    }

    // Show a single rate type with detailed information
    public function show($id)
    {
        $rateType = RateType::with(['rates', 'promotions', 'properties'])->find($id);
        return response()->json($rateType);
    }

    // Create a new rate type
    public function store(Request $request)
    {
        $rateType = RateType::create($request->all());
        // Additional logic for setting up and linking rate types with rates and promotions
        return response()->json($rateType, 201);
    }

    // Update a rate type's details
    public function update(Request $request, $id)
    {
        $rateType = RateType::findOrFail($id);
        $rateType->update($request->all());
        // Additional logic for managing rate type changes
        return response()->json($rateType, 200);
    }

    // Delete a rate type
    public function destroy($id)
    {
        RateType::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Linking rate types with specific promotions
    public function linkWithPromotion($rateTypeId, $promotionId)
    {
        // Logic to link a rate type with a specific promotion
    }

    // Example: Adjusting rates within a rate type
    public function adjustRates($rateTypeId, Request $request)
    {
        // Logic for adjusting rates within a specific rate type
    }
}

