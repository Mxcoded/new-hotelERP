<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Folio;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class CheckInController extends Controller
{
    // List all check-ins
    public function index()
    {
        $checkIns = CheckIn::with(['reservation.guest', 'reservation.room', 'guest', 'folios.charges', 'room'])->get();
        return response()->json($checkIns);
    }

    // Show a single check-in record
    public function show($id)
    {
        $checkIn = CheckIn::with(['reservation.guest', 'reservation.room', 'folios.charges', 'guest', 'room'])->find($id);
        return response()->json($checkIn);
    }

    // Record a new check-in
    // public function store(Request $request)
    // {
    //     // Validate the request data
    //     // ...

    //     // Check if the reservation exists and is valid for check-in
    //     $reservation = Reservation::find($request->reservation_id);
    //     if (!$reservation || $reservation->status != 'confirmed') {
    //         return response()->json(['message' => 'Invalid reservation for check-in'], 400);
    //     }

    //     // Create the CheckIn record
    //     $checkIn = CheckIn::create([
    //         'reservation_id' => $reservation->id,
    //         'check_in_time' => now(),
    //         // 'user_id' => $request->user_id, // Assuming the user performing the check-in is provided
    //         // Other check-in details...
    //     ]);

    //     // Update the reservation status to 'checked-in'
    //     $reservation->update(['status' => 'checked-in']);

    //     // Additional logic for room assignment, guest notifications, etc.
    //     // ...

    //     return response()->json($checkIn, 201);
    // }
    public function store(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'reservation_id' => 'required|exists:reservations,id',
        'room_id' => 'required|exists:rooms,id',
        // Additional validation rules as needed
    ]);

    try {
        DB::beginTransaction();

        // Check if the reservation exists and is valid for check-in
        $reservation = Reservation::find($validatedData['reservation_id']);
        if (!$reservation || $reservation->status != 'confirmed') {
            return response()->json(['message' => 'Invalid reservation for check-in'], 400);
        }

        // Check room availability
        $room = Room::find($validatedData['room_id']);
        if (!$room || $room->status !='available') {
            return response()->json(['message' => 'Room is not available'], 400);
        }

        // Create the CheckIn record
        $checkIn = CheckIn::create([
            'reservation_id' => $reservation->id,
            'check_in_time' => now(),
            'notes' =>$reservation->special_requests,
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id,


            // Other check-in details...
        ]);

        // Update the reservation status to 'checked-in'
        $reservation->update(['status' => 'checked-in']);

        // Create or update the folio for this stay
        $folio = Folio::updateOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'guest_id' => $reservation->guest_id,
                'check_in_id' => $checkIn->id, // Linking folio to the check-in record
                'date_created' => now(),
                'total_charges' => $reservation->price,
                'total_payments' => $reservation->amount_paid,
                'balance' => $reservation->balance_amount,
                'status' => 'Open'
                // Include other folio details as required
            ]
        );

        // Update room status
        $room->update(['status' => 'occupied']);

        // Additional logic for guest notifications, etc.
        // ...

        DB::commit();

        return response()->json(['message' => 'Guest checked in successfully', 'checkIn' => $checkIn], 201);

    } catch (Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Check-in process failed', 'error' => $e->getMessage()], 500);
    }
}



    // Update a check-in record (e.g., modify time, handle issues)
    public function update(Request $request, $id)
    {
        $checkIn = CheckIn::findOrFail($id);
        $checkIn->update($request->all());
        // Handle additional logic as necessary
        return response()->json($checkIn, 200);
    }

    // Delete a check-in record (if needed)
    public function destroy($id)
    {
        CheckIn::find($id)->delete();
        // Handle any additional cleanup
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Handling early or late check-ins
    public function handleSpecialCheckIn($id, Request $request)
    {
        // Logic for managing special check-in scenarios
    }
    public function handleDirectCheckIn(Request $request, GuestService $guestService)
    {
        $validatedData = $request->validate([
            'guest_id' => 'sometimes|exists:guests,id',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'advance_payment' => 'sometimes|numeric|min:0', // Optional advance payment
            'special_requests' => 'sometimes|nullable|string',
            'price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Find an available room based on room type, dates, and property
            $availableRoom = Room::where([
                ['room_type_id', $validatedData['room_type_id']],
                ['status', 'available'], // Here we use 'status' instead of 'is_available'
                ['property_id', $validatedData['property_id']],
            ])->first();

            if (!$availableRoom) {
                return response()->json(['message' => 'No available rooms for the selected date or room type'], 404);
            }

            // Handle guest information
            $guestId = $validatedData['guest_id'] ?? $this->createNewGuest($request->all(), $guestService);

            $reservationNumber = 'BP' . strtoupper(Str::random(8));
            // Create a new reservation
            $reservation = Reservation::create([
                'guest_id' => $guestId,
                'room_id' => $availableRoom->id,
                'check_in_date' => $validatedData['check_in_date'],
                'check_out_date' => $validatedData['check_out_date'],
                'status' => 'checked-in',
                'reservation_number' => $reservationNumber,
                // Other necessary fields
            ]);

            // Create a check-in record directly
            $checkIn = CheckIn::create([
                'guest_id' => $guestId,
                'room_id' => $availableRoom->id,
                'check_in_time' => now(),
                'notes' =>$validatedData['special_requests'],
                // other fields as needed
            ]);

            // Handle financials in the folio
            $roomRate = $availableRoom->base_price; // Assuming rate is defined in Room model
            $stayDuration = Carbon::parse($validatedData['check_in_date'])->diffInDays(Carbon::parse($validatedData['check_out_date']));
            $totalStayCost = $validatedData['price']; //$roomRate * $stayDuration;
            $advancePayment = $validatedData['advance_payment'] ?? 0;
            $remainingBalance = $totalStayCost - $advancePayment;

            $folio = Folio::create([
                'check_in_id' => $checkIn->id,
                'guest_id' => $guestId,
                'date_created' => now(),
                'total_charges' => $totalStayCost,
                'total_payments' => $advancePayment,
                'balance' => $remainingBalance,
                'status' => 'Open',
            ]);

            // Update room status
            $availableRoom->status = 'occupied';
            $availableRoom->save();

            DB::commit();

            return response()->json(['message' => 'Guest checked in successfully', 'checkIn' => $checkIn], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Direct check-in failed', 'error' => $e->getMessage()], 500);
        }
    }

    //List Checkin by property
    public function listByProperty($propertyId)
    {
        $checkIn = CheckIn::where('property_id', $propertyId)->get();
        return response()->json($checkIn);
    }
    //private function
    private function createNewGuest($data, GuestService $guestService)
    {
        $guest = $guestService->createGuest($data);
        return $guest->id;
    }


}
