<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    // List all maintenance requests
    public function index()
    {
        $requests = MaintenanceRequest::with(['room', 'property', 'user'])->get();
        return response()->json($requests);
    }

    // Show a single maintenance request with detailed information
    public function show($id)
    {
        $request = MaintenanceRequest::with(['room', 'property', 'user'])->find($id);
        return response()->json($request);
    }

    // Record a new maintenance request
    public function store(Request $request)
    {
        $maintenanceRequest = MaintenanceRequest::create($request->all());
        // Additional logic for scheduling repairs or notifying staff
        return response()->json($maintenanceRequest, 201);
    }

    // Update a maintenance request (e.g., status updates, adding notes)
    public function update(Request $request, $id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $maintenanceRequest->update($request->all());
        // Additional logic for handling changes in the maintenance task
        return response()->json($maintenanceRequest, 200);
    }

    // Delete a maintenance request
    public function destroy($id)
    {
        MaintenanceRequest::find($id)->delete();
        // Additional cleanup if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Assigning maintenance tasks to staff
    public function assignTask(Request $request)
    {
        // Logic for assigning specific maintenance tasks to staff members
    }

    // Example: Updating maintenance task completion
    public function updateTaskStatus($taskId, $status)
    {
        // Logic for updating the status of a maintenance task
    }
}
