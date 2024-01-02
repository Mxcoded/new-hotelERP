<?php

namespace App\Http\Controllers;

use App\Models\CheckOut;
use App\Models\CheckIn;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CheckOutController extends Controller
{
    // List all check-outs
    public function index()
    {
        $checkOuts = CheckOut::with(['reservation.guest', 'reservation.room', 'user'])->get();
        return response()->json($checkOuts);
    }

    // Show a single check-out record
    public function show($id)
    {
        $checkOut = CheckOut::with(['reservation.guest', 'reservation.room', 'user'])->find($id);
        return response()->json($checkOut);
    }

    // Record a new check-out
    public function store(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'check_in_id' => 'required|exists:check_ins,id',
            'notes' => 'nullable|string', // Validation rule for notes
        ]);

        try {
            DB::beginTransaction();

            // Fetch the check-in record
            $checkIn = CheckIn::with('reservation')->findOrFail($validatedData['check_in_id']);

            // Handle reservation if it exists
            if ($checkIn->reservation && $checkIn->reservation->status != 'checked-in') {
                return response()->json(['message' => 'Reservation is not in a checked-in state'], 400);
            }

            // Record the check-out
            $checkOut = CheckOut::create([
                'check_in_id' => $checkIn->id,
                'reservation_id' => $checkIn->reservation_id, // Linking to reservation if it exists
                'check_out_time' => now(),
                'notes' => $validatedData['notes'] ?? '', // Saving notes
                // Additional details...
            ]);

            // Update reservation status if it exists
            $checkIn->reservation?->update(['status' => 'checked-out']);

            // Update room availability
            $room = $checkIn->room;
            $room->update(['is_available' => true]);

            // Finalize folio
            $folio = $checkIn->folio; // Assuming a relationship is defined to link check-in to folio
            $folio->update(['status' => 'Closed']);

            DB::commit();

            return response()->json(['message' => 'Check-out successful', 'checkOut' => $checkOut], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Check-out process failed', 'error' => $e->getMessage()], 500);
        }
    }



    // Update a check-out record (e.g., handle late check-outs, billing adjustments)
    public function update(Request $request, $id)
    {
        $checkOut = CheckOut::findOrFail($id);
        $checkOut->update($request->all());
        // Additional logic for specific check-out scenarios
        return response()->json($checkOut, 200);
    }

    // Delete a check-out record (if necessary)
    public function destroy($id)
    {
        CheckOut::find($id)->delete();
        // Additional cleanup if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Finalizing billing and payments
    public function finalizeBilling($id, Request $request)
    {
        // Logic for finalizing the billing process for a check-out
    }

    //List Checkout by property
    public function listByProperty($propertyId)
    {
        $checkout = CheckOut::where('property_id', $propertyId)->get();
        return response()->json($checkout);
    }
}
