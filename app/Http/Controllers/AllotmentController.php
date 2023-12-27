<?php

namespace App\Http\Controllers;

use App\Models\Allotment;
use Illuminate\Http\Request;

class AllotmentController extends Controller
{
    // List all allotments
    public function index()
    {
        $allotments = Allotment::with(['group', 'property', 'roomType'])->get();
        return response()->json($allotments);
    }

    // Show a single allotment with detailed information
    public function show($id)
    {
        $allotment = Allotment::with(['group', 'property', 'roomType'])->find($id);
        return response()->json($allotment);
    }

    // Create a new allotment
    public function store(Request $request)
    {
        $allotment = Allotment::create($request->all());
        // Additional logic for setting up the allotment (e.g., assigning rooms)
        return response()->json($allotment, 201);
    }

    // Update an allotment's details
    public function update(Request $request, $id)
    {
        $allotment = Allotment::findOrFail($id);
        $allotment->update($request->all());
        // Additional logic for managing changes in the allotment
        return response()->json($allotment, 200);
    }

    // Delete an allotment
    public function destroy($id)
    {
        Allotment::find($id)->delete();
        // Additional cleanup logic if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Reallocating rooms within an allotment
    public function reallocateRooms($allotmentId, Request $request)
    {
        // Logic for adjusting room allocations within an allotment
    }
}

