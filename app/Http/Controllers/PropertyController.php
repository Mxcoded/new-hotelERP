<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    // List all properties with related data
    public function index()
    {
        $properties = Property::all();
        //$properties = Property::with(['rooms', 'users', 'reservations', 'amenities'])->get();
        return response()->json($properties);
    }

    // Show a single property with detailed information
    public function show($id)
    {
        $property = Property::findOrFail($id);
        // $property = Property::with(['rooms', 'users', 'reservations', 'amenities'])->find($id);
        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }
        return response()->json($property);
    }

    // Create a new property with initial setup
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:properties,name|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'exists:amenities,id',
            // Include other fields as needed
        ]);
        if ($validator->fails()) {
            // Handle validation failure, e.g., return a response with errors
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $PropertyData = $validator->validated();


        $Property = Property::create($PropertyData);

        if (isset($PropertyData['amenities'])) {
            $Property->amenities()->sync($PropertyData['amenities']);
        }
        if ($Property) {
            return response()->json(['message' => 'Property Added Successfully'], 201);
        } else {
            return response()->json(['message' => 'Property Not Added Successfully']);
        }

        // Additional logic for initial setup (e.g., assigning users, setting up rooms)
        //return response()->json($Property, 201);
    }

    // Update a property details
    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'amenities' => 'sometimes|array',
            'amenities.*' => 'exists:amenities,id',
            // Include other fields as needed
        ]);

        $property->update($validated);

        if (isset($validated['amenities'])) {
            $property->amenities()->sync($validated['amenities']);
        }

        // Additional logic for setting up rooms
        if (isset($request->rooms)) {
            // Assuming $request->rooms contains room data
            foreach ($request->rooms as $roomData) {
                $property->rooms()->create($roomData); // Ensure Room model has fillable attributes set
            }
        }

        // Additional logic for setting up facilities
        if (isset($request->facilities)) {
            // Handle facilities data
            // This depends on how you've structured facilities data and the relationship with the property
            // E.g., $property->facilities()->create($facilityData);
        }

        // Additional logic for updating related data (e.g., updating rooms, facilities)
        return response()->json($property, 200);
    }

    // Delete a property and handle related cleanup
    public function destroy($id)
    {
        $property = Property::with(['rooms', 'reservations'])->find($id);

        if ($property) {
            // Handle related cleanup (e.g., removing associated rooms, transferring reservations)
            $property->delete();
        }
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Fetching available rooms for a property
    public function availableRooms($propertyId)
    {
        $rooms = Room::where('property_id', $propertyId)
            ->whereDoesntHave('reservations', function ($query) {
                $query->where('status', 'occupied');
            })->get();

        return response()->json($rooms);
    }

    public function addAmenity(Request $request, $propertyId)
    {
        // Logic to add an amenity to the property
    }

    public function removeAmenity($propertyId, $amenityId)
    {
        // Logic to remove an amenity from the property
    }

    // Add a facility to a property
    public function addFacility(Request $request, $propertyId)
    {
        // Validate and create facility
    }

    // Update a facility
    public function updateFacility(Request $request, $propertyId, $facilityId)
    {
        // Validate and update facility
    }

    // Remove a facility
    public function removeFacility($propertyId, $facilityId)
    {
        // Delete facility
    }

    public function occupancyStatus($propertyId)
    {
        // Logic to calculate and return the occupancy status
    }
    public function maintenanceRequests($propertyId)
    {
        // Logic to handle maintenance requests for the property
    }
    public function manageEvents($propertyId)
    {
        // Logic for event management for the property
    }
    public function addRoom(Request $request, $propertyId)
    {
        // Logic to add a new room to the property
    }

    public function updateRoom(Request $request, $propertyId, $roomId)
    {
        // Logic to update room details
    }
    public function assignStaff(Request $request, $propertyId)
    {
        // Logic to assign staff to the property
    }


    // Example: Generating property-specific reports
    public function generateReport($propertyId)
    {
        // Logic to generate reports for the property
    }
    public function revenueReport($propertyId)
    {
        // Logic to generate a revenue report for the property
    }
}
