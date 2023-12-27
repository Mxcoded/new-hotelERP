<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyProgram;
use Illuminate\Http\Request;

class LoyaltyProgramController extends Controller
{
    // List all loyalty program entries
    public function index()
    {
        $programs = LoyaltyProgram::with(['guests', 'promotions', 'properties'])->get();
        return response()->json($programs);
    }

    // Show a single loyalty program entry with details
    public function show($id)
    {
        $program = LoyaltyProgram::with(['guests', 'promotions', 'properties'])->find($id);
        return response()->json($program);
    }

    // Create a new loyalty program
    public function store(Request $request)
    {
        $program = LoyaltyProgram::create($request->all());
        // Additional logic for setting up program benefits, linking to guests, etc.
        return response()->json($program, 201);
    }

    // Update a loyalty program's details
    public function update(Request $request, $id)
    {
        $program = LoyaltyProgram::findOrFail($id);
        $program->update($request->all());
        // Additional logic for updating program details and benefits
        return response()->json($program, 200);
    }

    // Delete a loyalty program entry
    public function destroy($id)
    {
        LoyaltyProgram::find($id)->delete();
        // Additional cleanup if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Adding a guest to a loyalty program
    public function enrollGuest($programId, $guestId)
    {
        // Logic to enroll a guest in a specific loyalty program
    }

    // Example: Updating loyalty points or rewards for a guest
    public function updatePoints($guestId, Request $request)
    {
        // Logic to update the loyalty points or rewards for a guest
    }
}

