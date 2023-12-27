<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if a property_id is provided
        $propertyId = $request->query('property_id');

        if ($propertyId) {
            // Fetch floors that belong to the provided property_id
            $floors = Floor::where('property_id', $propertyId)->with('rooms')->get();
        } else {
            // If no property_id is provided, fetch all floors
            $floors = Floor::all();
        }

        return response()->json($floors);
    }

    /**
     * Store a newly created resource in storage.
     */
    // Method to store a new floor and generate rooms
    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string',
            'start_room_number' => 'required|integer',
            'total_rooms' => 'required|integer|min:1'
        ]);

        // Handle transaction
        DB::beginTransaction();
        try {
            $floor = Floor::create($validated);
            $this->generateRoomsForFloor($floor);

            DB::commit();
            return response()->json($floor, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateRoomsForFloor(Floor $floor)
    {
        for ($i = 0; $i < $floor->total_rooms; $i++) {
            $roomNumber = $floor->start_room_number + $i;

            // Check for existing room number conflicts
            if (Room::where('number', $roomNumber)
                ->where('property_id', $floor->property_id)
                ->exists()
            ) {
                throw new \Exception("Room number conflict detected: $roomNumber");
            }

            Room::create([
                'property_id' => $floor->property_id,
                'floor_id' => $floor->id,
                'number' => $roomNumber,
                // Add other default room attributes here
            ]);
        }
    }





    /**
     * Display the specified floor along with related details.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Retrieve the floor with related data, e.g., rooms
            $floor = Floor::with('rooms') // Assuming a relationship is defined in the Floor model
                ->findOrFail($id);

            // Additional logic to include more details if needed
            // E.g., floor-specific services, amenities, etc.

            return response()->json($floor);
        } catch (ModelNotFoundException $e) {
            // Return a user-friendly error message if floor not found
            return response()->json(['message' => 'Floor not found'], 404);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Floor $floor)
    {
        DB::beginTransaction();

        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'sometimes|string',
                'start_room_number' => 'sometimes|integer',
                'total_rooms' => 'sometimes|integer|min:1',
                // Include other fields as necessary
            ]);

            // Update the floor with validated data
            $floor->update($validatedData);

            // If necessary, update related rooms
            if ($request->has('start_room_number') || $request->has('total_rooms')) {
                $this->updateRelatedRooms($floor, $validatedData);
            }

            DB::commit();

            // Reload the floor to reflect any changes in the related rooms
            $floor->load('rooms');

            // Return the updated floor data, including updated rooms
            return response()->json(['data' => $floor]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Floor update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Floor update failed'], 500);
        }
    }

    /**
     * Update the rooms associated with the floor.
     *
     * @param Floor $floor
     * @param array $validatedData
     * @return void
     */
    private function updateRelatedRooms(Floor $floor, array $validatedData)
    {
        try {
            // Get all rooms associated with the floor
            $rooms = $floor->rooms()->orderBy('id')->get();
            $existingRoomCount = $rooms->count();

            // Update existing rooms with incremented room numbers
            foreach ($rooms as $key => $room) {
                $newRoomNumber = $validatedData['start_room_number'] + $key;
                // Check for room number conflicts
                if ($this->checkRoomNumberConflict($newRoomNumber, $floor->property_id, $room->id)) {
                    throw new \Exception("Room number conflict detected: $newRoomNumber");
                }
                $room->number = $newRoomNumber;
                $room->save();
            }

            // Adjust the total number of rooms
            $this->adjustRoomCount($floor, $existingRoomCount, $floor->property_id);
        } catch (\Exception $e) {
            Log::error('Error updating related rooms for floor: ' . $floor->id . ' - ' . $e->getMessage());
            throw $e; // Rethrow the exception to be handled by the caller
        }
    }

    /**
     * Check for room number conflicts.
     *
     * @param int $roomNumber
     * @param int $propertyId
     * @param int $currentRoomId
     * @return bool
     */
    private function checkRoomNumberConflict($roomNumber, $propertyId, $currentRoomId)
    {
        return Room::where('number', $roomNumber)
            ->where('property_id', $propertyId)
            ->where('id', '!=', $currentRoomId)
            ->exists();
    }




    private function adjustRoomCount(Floor $floor, $existingRoomCount, $propertyId)
    {
        // Determine the number of rooms to be added or removed
        $roomsDifference = $floor->total_rooms - $existingRoomCount;

        if ($roomsDifference > 0) {
            // Add additional rooms
            for ($i = 0; $i < $roomsDifference; $i++) {
                $roomNumber = $floor->start_room_number + $existingRoomCount + $i;
                Room::create([
                    'property_id' => $propertyId,
                    'floor_id' => $floor->id,
                    'number' => $roomNumber,
                    // Include other default or necessary attributes for the room
                ]);
            }
        } elseif ($roomsDifference < 0) {
            // Remove excess rooms, targeting the highest room numbers first
            $roomsToRemove = Room::where('floor_id', $floor->id)
                ->orderBy('number', 'desc')
                ->limit(abs($roomsDifference))
                ->get();

            foreach ($roomsToRemove as $room) {
                $room->delete();
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    //public function destroy(Floor $floor)
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $floor = Floor::findOrFail($id);

            // Delete related rooms first
            $floor->rooms()->delete();

            // Now delete the floor
            $floor->delete();

            DB::commit();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            // Return a user-friendly error message if floor not found
            return response()->json(['message' => 'Floor not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Floor deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Floor deletion failed'], 500);
        }
    }
}
