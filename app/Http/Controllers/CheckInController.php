<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Folio;
use App\Services\FolioService;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use illuminate\Support\Facades\Auth;
use illuminate\Support\Str;
use Carbon\Carbon;
use Exception;


class CheckInController extends Controller
{

    // List check-ins for reservations that are currently checked in and belong to the property of the signed-in user
    public function index()
    {
        // Get the property ID of the signed-in user
        $currentUserPropertyId = Auth::user()->property_id;

        $checkIns = CheckIn::with(['reservation.guest', 'reservation.room', 'reservation.property', 'guest', 'folios.charges', 'room'])
            ->whereHas('reservation', function ($query) use ($currentUserPropertyId) {
                $query->where('status', 'checked-in')->where('property_id', $currentUserPropertyId);
            })
            ->get();

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
        // Validate the request data, including optional payment information
        $validatedData = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'room_id' => 'required|exists:rooms,id',
            'payment_amount' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|string',
            // Additional validation rules as needed
        ]);

        try {
            DB::beginTransaction();

            $reservation = Reservation::find($validatedData['reservation_id']);
            if (!$reservation || $reservation->status != 'confirmed') {
                return response()->json(['message' => 'Invalid reservation for check-in'], 400);
            }

            $room = Room::find($validatedData['room_id']);
            if (!$room || $room->status != 'reserved') {
                return response()->json(['message' => 'Room is not available'], 400);
            }

            // initiate folio Service
            $folioService = new FolioService();
            // Update guest's total balance and check for outstanding balance
            $guest = $reservation->guest; // Assuming 'guest' relation in Reservation
            $guestOutstandingBalance = $folioService->updateGuestBalance($guest->id);

            // Handle scenarios where the guest has an outstanding balance
            if ($guestOutstandingBalance > 0) {
                // Implement your business logic here, e.g., deny check-in, request immediate payment, etc.
            }

            $checkIn = CheckIn::create([
                'reservation_id' => $reservation->id,
                'check_in_time' => now(),
                'notes' => $reservation->special_requests,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                // Other check-in details...
            ]);

            $reservation->update(['status' => 'checked-in']);

            $folio = Folio::updateOrCreate(
                ['reservation_id' => $reservation->id],
                [
                    'guest_id' => $reservation->guest_id,
                    'check_in_id' => $checkIn->id,
                    'date_created' => now(),
                    'total_charges' => $reservation->price,
                    'total_payments' => $reservation->amount_paid,
                    'balance' => $reservation->balance_amount,
                    'status' => 'Open'
                ]
            );


            // Initialize handleEarlyCheckIn Charges
            $folioService->handleEarlyCheckIn($checkIn->id);

            // Handle payment at check-in
            if (isset($validatedData['payment_amount']) && $validatedData['payment_amount'] > 0) {
                $folioService->recordPayment(
                    $folio->id,
                    $reservation->guest_id,
                    $reservation->property_id,
                    $validatedData['payment_amount'],
                    $validatedData['payment_method']
                );

                // Update guest balance after payment
                $folioService->updateGuestBalance($guest->id);
            }


            $room->update(['status' => 'occupied']);

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
        $validatedData = $request->validate([
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'room_id' => 'sometimes|exists:rooms,id',
            // Include other fields that might need validation
        ]);

        try {
            DB::beginTransaction();

            $checkIn = CheckIn::with(['reservation', 'room'])->findOrFail($id);

            // Ensure the reservation is not already checked out
            if ($checkIn->reservation->status === 'checked-out') {
                throw new Exception('Cannot extend a stay after check-out.');
            }

            // Update the reservation check-out date
            $checkIn->reservation->update(['check_out_date' => $validatedData['check_out_date']]);

            // If room change is requested
            if (isset($validatedData['room_id']) && $validatedData['room_id'] != $checkIn->room_id) {
                // Update old room status to 'available'
                $checkIn->room->update(['status' => 'available']);

                // Assign new room and update status
                $checkIn->update(['room_id' => $validatedData['room_id']]);
                Room::where('id', $validatedData['room_id'])->update(['status' => 'occupied']);
            }

            // If the check-out date is extended
            if ($validatedData['check_out_date'] !== $checkIn->reservation->check_out_date) {
                // Assuming $folioService is an instance of FolioService
                // You might need to resolve this instance from the service container or pass it as a dependency
                $folioService = new FolioService();
                $folioService->recalculateChargesForExtendedStay($checkIn->reservation->folio_id, $validatedData['check_out_date']);
            }

            DB::commit();
            return response()->json(['success' => true, 'checkIn' => $checkIn], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            'advance_payment' => 'sometimes|numeric|min:0',
            'special_requests' => 'sometimes|nullable|string',
            'price' => 'required|numeric',
            'property_id' => 'required|exists:properties,id', // Ensure property_id is validated
            // Additional fields as required
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
            // Create a new reservation
            $reservation = $this->createDirectCheckInReservation($validatedData, $guestService);

            // // Handle guest information
            // $guestId = $validatedData['guest_id'] ?? $this->createNewGuest($request->all(), $guestService);

            // $reservationNumber = 'BP' . strtoupper(Str::random(8));
            // // Create a new reservation
            // $reservation = Reservation::create([
            //     'guest_id' => $guestId,
            //     'room_id' => $availableRoom->id,
            //     'check_in_date' => $validatedData['check_in_date'],
            //     'check_out_date' => $validatedData['check_out_date'],
            //     'status' => 'checked-in',
            //     'reservation_number' => $reservationNumber,
            //     // Other necessary fields
            // ]);

            // Create a check-in record directly
            $checkIn = CheckIn::create([
                'reservation_id' => $reservation->id,
                'check_in_time' => now(),
                'check_in_date' => $validatedData['check_in_date'],
                'check_out_date' => $validatedData['check_out_date'],
                'notes' => $validatedData['special_requests'] ?? null,
                'guest_id' => $reservation->guest_id,
                'room_id' => $reservation->room_id,
                'status' => 'checked-in', // Assuming 'checked-in' status
                'checked_in_by' => Auth::id(), // Assuming current user
                'expected_check_out_time' => $validatedData['check_out_date'], // Assuming check-out date as expected time
                'room_key_card' => null, // Set this as per your logic
                'is_group_check_in' => false, // Default or derived from request
                'additional_guests' => null, // Default or derived from request
                // other fields as needed
            ]);

            // Initialize folio and handle financials
            $folioService = new FolioService();
            $folioService->initializeFolioForDirectCheckIn($checkIn, $validatedData, $reservation);

            // // Handle financials in the folio
            // $roomRate = $availableRoom->base_price; // Assuming rate is defined in Room model
            // $stayDuration = Carbon::parse($validatedData['check_in_date'])->diffInDays(Carbon::parse($validatedData['check_out_date']));
            // $totalStayCost = $validatedData['price']; //$roomRate * $stayDuration;
            // $advancePayment = $validatedData['advance_payment'] ?? 0;
            // $remainingBalance = $totalStayCost - $advancePayment;

            // $folio = Folio::create([
            //     'check_in_id' => $checkIn->id,
            //     'guest_id' => $reservation->guest_id,
            //     'date_created' => now(),
            //     'total_charges' => $totalStayCost,
            //     'total_payments' => $advancePayment,
            //     'balance' => $remainingBalance,
            //     'status' => 'Open',
            // ]);

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
    private function createDirectCheckInReservation($data, GuestService $guestService)
    {
        $guestId = $data['guest_id'] ?? $guestService->createGuest($data)->id;
        $room = Room::where('room_type_id', $data['room_type_id'])->where('status', 'available')->firstOrFail();
        // Calculate the number of nights between check-in and check-out dates
        $checkInDate = Carbon::parse($data['check_in_date']);
        $checkOutDate = Carbon::parse($data['check_out_date']);
        $totalNights = $checkOutDate->diffInDays($checkInDate);

        // Ensure that total nights is never negative or zero
        $totalNights = max($totalNights, 1);

        // Rest of your reservation creation logic
        $reservationNumber = 'BP' . strtoupper(Str::random(8));

        return Reservation::create([
            'guest_id' => $guestId,
            'room_id' => $room->id,
            'reservation_date' => now(),
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'number_of_guests' => 1, // Default or derived from request
            'price' => $data['price'],
            'status' => 'confirmed',
            'payment_method' => 'unpaid', // Default or derived from request
            'payment_status' => 'pending', // Default or derived from request
            'amount_paid' => $data['advance_payment'] ?? 0,
            'balance_amount' => $data['price'] - ($data['advance_payment'] ?? 0),
            'special_requests' => $data['special_requests'] ?? null,
            'property_id' => $data['property_id'],
            'total_nights' => $totalNights,
            'reservation_number' => $reservationNumber,
            // Other fields as needed
        ]);
    }

    // List Check-ins by property in descending order
    public function listByProperty($propertyId)
    {
        $checkIns = CheckIn::with(['reservation.guest', 'reservation.room', 'guest', 'folios.charges', 'room'])
            ->whereHas('reservation', function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();

        return response()->json($checkIns);
    }



    //List Checkin by room
    public function listByRooms($roomId)
    {
        $checkIns = CheckIn::whereHas('reservation', function ($query) use ($roomId) {
            $query->where('room_id', $roomId);
        })->first();

        return response()->json($checkIns);
    }

    //private function
    private function createNewGuest($data, GuestService $guestService)
    {
        $guest = $guestService->createGuest($data);
        return $guest->id;
    }
}
