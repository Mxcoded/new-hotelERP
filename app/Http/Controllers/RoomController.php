<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // List all rooms with additional details
    public function index()
    {
        $rooms = Room::with(['property', 'roomType', 'reservations'])->get();
        return response()->json($rooms);
    }

    // Show a single room with detailed information
    public function show($id)
    {
        $room = Room::with(['property', 'roomType', 'reservations', 'maintenanceRequests'])->find($id);
        return response()->json($room);
    }

    // // Create a new room within a property
    // public function store(Request $request)
    // {
    //     $room = Room::create($request->all());
    //     // Additional setup logic if required
    //     return response()->json($room, 201);
    // }

    // Update a room's details
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'room_type_id' => 'sometimes|nullable|exists:room_types,id',
            'property_id' => 'sometimes|required|exists:properties,id',
            'floor_id' => 'sometimes|nullable|exists:floors,id',
            'number' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'is_available' => 'sometimes|nullable|required|boolean',
            // Validate additional fields as necessary
        ]);

        $room = Room::findOrFail($id);
        $room->update($validatedData);

        // Optionally, handle any additional specific logic if required
        // For example, updating related entities or triggering events

        return response()->json($room, 200);
    }


    // Delete a room
    public function destroy($id)
    {
        Room::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for room-specific operations...

    // Example: Updating room status (e.g., maintenance, cleaning)
    public function updateStatus($id, Request $request)
    {
        $room = Room::findOrFail($id);
        $room->update(['status' => $request->status]); // e.g., 'available', 'under maintenance'
        return response()->json($room);
    }

    // Example: Listing rooms based on their status
    public function listByStatus($status)
    {
        $rooms = Room::where('status', $status)->with('roomType')->get();
        return response()->json($rooms);
    }

    /**
     * List rooms by room type ID.
     *
     * @param int $roomTypeId The ID of the room type.
     * @return \Illuminate\Http\JsonResponse
     */
    public function listRoomsByType($roomTypeId)
    {
        try {
            // Retrieve rooms with the specified room type ID
            $rooms = Room::where('room_type_id', $roomTypeId)->get();

            // Check if rooms are found
            if ($rooms->isEmpty()) {
                return response()->json(['message' => 'No rooms found for the specified type.'], 404);
            }

            return response()->json($rooms);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return response()->json(['error' => 'An error occurred while retrieving rooms: ' . $e->getMessage()], 500);
        }
    }
}
