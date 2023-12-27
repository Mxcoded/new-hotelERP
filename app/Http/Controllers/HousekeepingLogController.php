<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingLog;
use Illuminate\Http\Request;

class HousekeepingLogController extends Controller
{
    // List all housekeeping logs
    public function index()
    {
        $logs = HousekeepingLog::with(['room', 'property', 'user'])->get();
        return response()->json($logs);
    }

    // Show a single housekeeping log with details
    public function show($id)
    {
        $log = HousekeepingLog::with(['room', 'property', 'user'])->find($id);
        return response()->json($log);
    }

    // Record a new housekeeping activity
    public function store(Request $request)
    {
        $log = HousekeepingLog::create($request->all());
        // Additional logic for updating room status or assigning tasks
        return response()->json($log, 201);
    }

    // Update a housekeeping log (e.g., mark as completed, add notes)
    public function update(Request $request, $id)
    {
        $log = HousekeepingLog::findOrFail($id);
        $log->update($request->all());
        // Additional logic as necessary for housekeeping management
        return response()->json($log, 200);
    }

    // Delete a housekeeping log entry
    public function destroy($id)
    {
        HousekeepingLog::find($id)->delete();
        // Additional cleanup if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Assigning housekeeping tasks to staff
    public function assignTask(Request $request)
    {
        // Logic for assigning specific housekeeping tasks to staff members
    }

    // Example: Updating room readiness status
    public function updateRoomStatus($roomId, $status)
    {
        // Logic for updating the status of a room (e.g., clean, needs maintenance)
    }
}

