<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // List all promotions
    public function index()
    {
        $promotions = Promotion::with(['roomTypes', 'properties'])->get();
        return response()->json($promotions);
    }

    // Show a single promotion with detailed information
    public function show($id)
    {
        $promotion = Promotion::with(['roomTypes', 'properties'])->find($id);
        return response()->json($promotion);
    }

    // Create a new promotion
    public function store(Request $request)
    {
        $promotion = Promotion::create($request->all());
        // Additional logic for linking promotion to room types or properties
        return response()->json($promotion, 201);
    }

    // Update a promotion's details
    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update($request->all());
        // Additional logic for managing changes in the promotion
        return response()->json($promotion, 200);
    }

    // Delete a promotion
    public function destroy($id)
    {
        Promotion::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Linking promotion to specific room types or properties
    public function linkToRoomType($promotionId, $roomTypeId)
    {
        // Logic to associate a promotion with a specific room type
    }

    // Example: Activating or deactivating a promotion
    public function toggleActivation($promotionId)
    {
        // Logic to activate or deactivate a promotion
    }
}

