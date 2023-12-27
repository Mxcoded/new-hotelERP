<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoomTypeController extends Controller
{
    // List all room types, optionally filtered by property
    public function index(Request $request)
    {
        if ($request->has('property_id')) {
            $roomTypes = RoomType::where('property_id', $request->property_id)->with('rooms')->get();
        } else {
            $roomTypes = RoomType::with('rooms')->get();
        }
        return response()->json($roomTypes);
    }

    // Show a single room type with details
    public function show($id)
    {
        try {
            $roomType = RoomType::with('rooms')->findOrFail($id);
            return response()->json($roomType);
        } catch (ModelNotFoundException $e) {
            // Return a user-friendly error message if floor not found
            return response()->json(['message' => 'Room Type not found'], 404);
        }
    }

    // Create a new room type within a specific propertypublic function store(Request $request)
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric',
            'extra_person_charge' => 'nullable|numeric',
            'extra_bed_charge' => 'nullable|numeric',
            'max_occupancy' => 'required|integer',
            'bed_type' => 'required|string',
            'is_accessible' => 'required|boolean',
            'amenities' => 'nullable|json',
            'size_type' => 'nullable|string',
            'view' => 'nullable|string',
            'property_id' => 'nullable|exists:properties,id' // Ensure property_id exists if provided
        ]);

        // Create the room type
        $roomType = RoomType::create($validatedData);

        // Attach a room type to a property if property_id is provided
        if (!empty($validatedData['property_id'])) {
            $property = Property::find($validatedData['property_id']);

            // Assuming $roomType is a single instance of RoomType
            $property->roomTypes()->saveMany([$roomType]);
        }

        return response()->json($roomType, 201);
    }
    // Update a room type's details
    public function update(Request $request, $id)
    {
        $roomType = RoomType::findOrFail($id);
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'base_price' => 'sometimes|required|numeric',
            'extra_person_charge' => 'sometimes|nullable|numeric',
            'extra_bed_charge' => 'sometimes|nullable|numeric',
            'max_occupancy' => 'sometimes|required|integer',
            'bed_type' => 'sometimes|required|string',
            'is_accessible' => 'sometimes|required|boolean',
            'amenities' => 'sometimes|nullable|json',
            'size_type' => 'sometimes|nullable|string',
            'view' => 'sometimes|nullable|string',
            'property_id' => 'sometimes|nullable|exists:properties,id' // Ensure property_id exists if provided
        ]);

        $roomType->update($request->all());
        return response()->json($roomType, 200);
    }

    // Delete a room type
    public function destroy($id)
    {
        RoomType::find($id)->delete();
        return response()->json(null, 204);
    }

    // Additional methods as needed...

    // Example: List room types for a specific property
    public function listByProperty($propertyId)
    {
        $roomTypes = RoomType::where('property_id', $propertyId)->get();
        return response()->json($roomTypes);
    }
}
