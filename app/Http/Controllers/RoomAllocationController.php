<?php

namespace App\Http\Controllers;

use App\Models\RoomAllocation;
use Illuminate\Http\Request;

class RoomAllocationController extends Controller
{
    // List all room allocations
    public function index()
    {
        $allocations = RoomAllocation::with(['room', 'reservation', 'guest', 'property'])->get();
        return response()->json($allocations);
    }

    // Show a single room allocation with details
    public function show($id)
    {
        $allocation = RoomAllocation::with(['room', 'reservation', 'guest', 'property'])->find($id);
        return response()->json($allocation);
    }

    // Create a new room allocation for a reservation
    public function store(Request $request)
    {
        $allocation = RoomAllocation::create($request->all());
        // Additional logic for room assignment based on reservation requirements
        return response()->json($allocation, 201);
    }

    // Update a room allocation's details
    public function update(Request $request, $id)
    {
        $allocation = RoomAllocation::findOrFail($id);
        $allocation->update($request->all());
        // Additional logic for handling changes in room allocation
        return response()->json($allocation, 200);
    }

    // Delete a room allocation
    public function destroy($id)
    {
        RoomAllocation::find($id)->delete();
        // Additional cleanup logic if required, like updating room status
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Allocating a specific room to a guest
    public function allocateRoomToGuest($allocationId, Request $request)
    {
        // Logic for assigning a specific room to a guest's reservation
    }

    // Example: Changing room allocation for an existing reservation
    public function changeRoomAllocation($allocationId, Request $request)
    {
        // Logic for changing the room allocated to a reservation
    }
}

