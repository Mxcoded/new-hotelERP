<?php

namespace App\Http\Controllers;

use App\Models\GuestPreference;
use Illuminate\Http\Request;

class GuestPreferenceController extends Controller
{
    // List all guest preferences
    public function index()
    {
        $preferences = GuestPreference::with(['guest', 'room', 'roomType', 'property'])->get();
        return response()->json($preferences);
    }

    // Show a single guest preference with details
    public function show($id)
    {
        $preference = GuestPreference::with(['guest', 'room', 'roomType', 'property'])->find($id);
        return response()->json($preference);
    }

    // Record new guest preferences
    public function store(Request $request)
    {
        $preference = GuestPreference::create($request->all());
        // Additional logic for linking preferences to guest profiles or reservations
        return response()->json($preference, 201);
    }

    // Update guest preferences
    public function update(Request $request, $id)
    {
        $preference = GuestPreference::findOrFail($id);
        $preference->update($request->all());
        // Additional logic for updating preference details
        return response()->json($preference, 200);
    }

    // Delete a guest preference entry
    public function destroy($id)
    {
        GuestPreference::find($id)->delete();
        // Additional cleanup if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Matching room availability with guest preferences
    public function matchRoomPreferences($guestId)
    {
        // Logic to find rooms that match a specific guest's preferences
    }
}
