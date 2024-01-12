<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\Folio;
use App\Models\folio_charges;
use App\Models\Guest;
use App\Models\Payment;
use App\Services\FolioService;
use Illuminate\Http\Request;
use Illuminate\Support\str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Mail\ReservationConfirmation;
use Exception;
use Carbon\Carbon;



class ReservationController extends Controller
{
    // List all reservations
    public function index()
    {

        $reservations = Reservation::with(['guests', 'room', 'property'])->get();
        return response()->json($reservations);
    }

    // Show a single reservation with detailed information
    public function show($id)
    {
        $reservation = Reservation::with(['guests', 'room', 'property'])->find($id);
        return response()->json($reservation);
    }

    // Create a new reservation
    /**
     * Store a newly created reservation in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Calculate total nights before validation
        if ($request->has(['check_in_date', 'check_out_date'])) {
            $checkInDate = Carbon::parse($request->input('check_in_date'));
            $checkOutDate = Carbon::parse($request->input('check_out_date'));
            $totalNights = $checkOutDate->diffInDays($checkInDate);
            $totalNights = max($totalNights, 1); // Ensure at least one night
            $request->merge(['total_nights' => $totalNights]);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'reservation_date' => 'required|date',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'number_of_guests' => 'required|integer',
            'price' => 'required|numeric',
            'status' => 'required|string',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'amount_paid' => 'required|numeric',
            'price_discount' => 'sometimes|numeric',
            'balance_amount' => 'required|numeric',
            'special_requests' => 'nullable|string',
            'total_nights' => 'required|integer',
            'cancellation_policy_id' => 'required|exists:cancellation_policies,id',
            'property_id' => 'required|exists:properties,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();

        try {
            DB::beginTransaction();

            // Find an available room based on room type, dates, and property
            $availableRoom = Room::where([
                ['status', 'available'],
                ['property_id', $validatedData['property_id']],
            ])->first();

            if (!$availableRoom) {
                return response()->json(['message' => 'No available rooms for the selected date or room type'], 404);
            }

            // Create the reservation
            $reservationNumber = 'BP' . strtoupper(Str::random(8));
            $validatedData['reservation_number'] = $reservationNumber;

            $reservation = Reservation::create($validatedData);

            // Assuming you receive an array of guest IDs with the request
            $guestIds = $request->input('guest_id', []);

            // Attach guests to the reservation
            $reservation->guests()->attach($guestIds);

            // Create a new folio for the reservation
            $folio = Folio::create([
                'reservation_id' => $reservation->id,
                'guest_id' => $reservation->guest_id,
                'date_created' => now(),
                'total_charges' => $reservation->price,
                'total_payments' => $reservation->amount_paid,
                'balance' => $reservation->balance_amount,
            ]);

            // Update reservation with the 'folio_id'
            $reservation->folio_id = $folio->id;
            $reservation->save();

            // Make sure to use the created folio's ID
            $folioId = $folio->id;
            $folioService = new FolioService();

            // Calculate daily rate based on total reservation cost and number of nights
            $dailyRate = $folioService->getDailyRate($availableRoom->id);

            // Apply the first day's accommodation charge
            $folioService->addChargeToFolio($folioId, 'Daily Accommodation', $dailyRate);

            // Check if there's an initial payment and handle it
            if ($validatedData['amount_paid'] > 0) {
                // Create the initial payment record
                Payment::create([
                    'reservation_id' => $reservation->id ?? null,
                    'folio_id' => $folio->id,
                    'guest_id' => $reservation->guest_id,
                    'property_id' => $validatedData['property_id'],
                    'amount' => $validatedData['amount_paid'],
                    'payment_method' => $validatedData['payment_method'],
                    'payment_date' => now(),
                    'transaction_id' => Str::uuid(),
                    'status' => 'completed',
                    'notes' => 'Initial reservation deposit',
                    'processed_by' => auth()->user()->id,
                ]);
            }

            // Send confirmation email to the guest
            // Retrieve guest information for email
            $guest = Guest::find($reservation->guest_id);
            if ($guest && $guest->email) {
                Mail::to($guest->email)->send(new ReservationConfirmation($reservation));
            }

            // Update room status to reserved
            Room::where('id', $validatedData['room_id'])->update(['status' => 'reserved']);

            DB::commit();
            return response()->json(['success' => true, 'data' => $reservation], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // Other controller methods...


    /**
     * Handle payment based on the payment method.
     *
     * @param Reservation $reservation
     * @param array $validatedData
     * @return void
     */
    // private function handlePayment($reservation, $validatedData)
    // {
    //     if ($validatedData['payment_method'] === 'online') {
    //         // Process online payment
    //         // Update $reservation->payment_status based on the payment gateway response
    //     } elseif ($validatedData['payment_method'] === 'bank_transfer') {
    //         // Handle bank transfer
    //     }

    //     // Additional payment method handling

    //     $reservation->save();
    // }


    // Update a reservation
    // public function update(Request $request, $id)
    // {
    //     $reservation = Reservation::findOrFail($id);
    //     $reservation->update($request->all());
    //     // Additional logic for handling changes in reservation details
    //     return response()->json($reservation, 200);
    // }
    // Update a reservation
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $reservation = Reservation::with(['room', 'folio'])->findOrFail($id);

            if ($reservation->status === 'checked-in') {
                throw new Exception('Cannot update a reservation that is already checked-in.');
            }

            $validator = Validator::make($request->all(), [
                'guest_id' => 'sometimes|exists:guests,id',
                'room_id' => 'sometimes|exists:rooms,id',
                'property_id' => 'sometimes|exists:properties,id', // Added validation for property_id
                'check_in_date' => 'sometimes|date',
                'check_out_date' => 'sometimes|date|after_or_equal:check_in_date',
                'number_of_guests' => 'sometimes|integer',
                'price' => 'sometimes|numeric',
                'status' => 'sometimes|string',
                'payment_method' => 'sometimes|string',
                'payment_status' => 'sometimes|string',
                'amount_paid' => 'sometimes|numeric',
                // Other fields...
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $validatedData = $validator->validated();

            $reservation->update($validatedData);

            // Recalculate total nights if dates have changed
            if ($request->has('check_in_date') || $request->has('check_out_date')) {
                $checkInDate = Carbon::parse($request->input('check_in_date', $reservation->check_in_date));
                $checkOutDate = Carbon::parse($request->input('check_out_date', $reservation->check_out_date));
                $totalNights = $checkOutDate->diffInDays($checkInDate);

                // Ensuring total nights is not negative or zero
                $reservation->total_nights = max($totalNights, 1);
            }

            // Folio and Payment Logic
            $folioService = new FolioService();
            $folio = $reservation->folio;
            if ($folio) {
                $folioService->updateFolioDetails($folio, $validatedData);
            }

            // Handle additional payment or refunds
            if ($request->has('additional_payment')) {
                $folioService->recordPayment(
                    $folio->id,
                    $reservation->guest_id,
                    $reservation->property_id,
                    $request->additional_payment,
                    $validatedData['payment_method'] ?? 'unknown'
                );
            }
            $reservation->save();
            // Update room and property status
            $this->updateRoomAndPropertyStatus($reservation, $validatedData);

            // Notification logic...
            // Audit trail logic...

            DB::commit();
            return response()->json(['success' => true, 'data' => $reservation], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function updateRoomAndPropertyStatus($reservation, $validatedData)
    {
        // Update room status
        if (isset($validatedData['room_id']) && $validatedData['room_id'] != $reservation->room_id) {
            Room::where('id', $reservation->room_id)->update(['status' => 'available']);
            Room::where('id', $validatedData['room_id'])->update(['status' => 'reserved']);
        }

        // Update property status if necessary
        // Additional logic as per business requirements
    }





    // Cancel or delete a reservation
    public function destroy($id)
    {
        Reservation::find($id)->delete();
        // Additional logic for handling cancellation effects, if needed
        return response()->json(null, 204);
    }

    // Additional methods as needed...

    // Example: Checking reservation availability
    public function checkAvailability(Request $request)
    {
        // Logic to check room availability for the requested dates and room type
    }

    // Example: Handling special requests or modifications
    public function handleSpecialRequest($id, Request $request)
    {
        // Logic to handle special requests for a reservation
    }

    public function markAsNoShow($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $reservation->status = 'no-show';  // Assuming 'no-show' is the correct status string
        $reservation->save();

        // Release the room associated with the reservation
        $room = Room::find($reservation->room_id);
        if ($room) {
            $room->status = 'available';  // Update status to make the room available again
            $room->save();
        }

        // Apply no-show charges if applicable
        $folio = Folio::where('reservation_id', $reservationId)->first();
        if ($folio) {
            $this->applyNoShowCharges($folio, $reservation);
        }

        // Additional business logic as needed
        // ...

        return response()->json(['message' => 'Reservation marked as no-show successfully']);
    }

    /**
     * Apply no-show charges to the reservation's folio.
     *
     * @param Folio $folio
     * @param Reservation $reservation
     */
    private function applyNoShowCharges(Folio $folio, Reservation $reservation)
    {
        $noShowFee = 5000;

        // Add a no-show charge to the folio
        $folioCharge = new Folio_charges([
            'folio_id' => $folio->id,
            'description' => 'No-Show Fee',
            'amount' => $noShowFee,
            'charge_date' => now(),
        ]);
        $folioCharge->save();

        // Update folio's total charges
        $folio->total_charges += $noShowFee;
        $folio->balance += $noShowFee;
        $folio->save();
    }

    public function cancelReservation($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $reservation->status = 'canceled';  // Assuming 'canceled' is the correct status string
        $reservation->save();

        // Make the room available again
        $room = Room::find($reservation->room_id);
        if ($room) {
            $room->status = 'available';
            $room->save();
        }

        // Calculate the time difference between now and the check-in date
        $checkInDateTime = Carbon::parse($reservation->check_in_date);
        $hoursUntilCheckIn = Carbon::now()->diffInHours($checkInDateTime, false);

        // Apply cancellation charges if within 48 hours
        if ($hoursUntilCheckIn <= 48) {
            $this->applyCancellationCharges($reservation);
        } else {
            // Process refund if there was a prepayment
            $this->processFullRefund($reservation);
        }

        // Additional logic as needed
        // ...

        return response()->json(['message' => 'Reservation canceled successfully']);
    }

    /**
     * Apply cancellation charges for the reservation.
     *
     * @param Reservation $reservation
     */
    private function applyCancellationCharges(Reservation $reservation)
    {
        $cancellationFee = 10000; // Calculate cancellation fee based on 5-star hotel policy

        // Add a cancellation charge to the reservation's folio
        $folioCharge = new folio_charges([
            'folio_id' => $reservation->folio_id,
            'description' => 'Cancellation Fee',
            'amount' => $cancellationFee,
            'charge_date' => now(),
        ]);
        $folioCharge->save();

        // Update folio's total charges
        $folio = Folio::find($reservation->folio_id);
        if ($folio) {
            $folio->total_charges += $cancellationFee;
            $folio->balance += $cancellationFee;
            $folio->save();
        }
    }

    /**
     * Process refund if there was a prepayment for the reservation.
     *
     * @param Reservation $reservation
     */
    private function processFullRefund(Reservation $reservation)
    {
        // Implement your refund logic here
        // This could involve interfacing with your payment gateway to process the refund
        // Or updating your internal records if the refund is handled manually
        // This might involve returning funds to the guest's payment method or crediting their account
        // Retrieve the folio associated with the reservation
        $folio = Folio::where('reservation_id', $reservation->id)->first();

        if ($folio && $folio->total_payments > 0) {
            // Assuming the total amount paid is to be refunded
            $refundAmount = $folio->total_payments;

            // Create a refund record in the system
            $refund = new Payment([
                'folio_id' => $folio->id,
                'guest_id' => $reservation->guest_id,
                'amount' => -$refundAmount, // Negative amount to indicate a refund
                'payment_method' => 'manual', // Indicate that this is a manual refund
                'payment_date' => now(),
                'transaction_id' => Str::uuid(), // Unique identifier for the transaction
                'status' => 'refunded',
                'notes' => 'Manual refund processed',
                'processed_by' => Auth::id(), // ID of the user processing the refund
            ]);
            $refund->save();

            // Update the folio's payment total and balance
            $folio->total_payments -= $refundAmount;
            $folio->balance = $folio->total_charges - $folio->total_payments;
            $folio->save();
        } else {
            // Handle scenarios where there are no payments to refund
            // This could be logging an error or notifying the appropriate staff
            return response()->json(['message' => 'Nothing to refund']);
        }
    }
    public function listByProperty($propertyId)
    {
        $reservations = Reservation::with(['guest', 'room', 'folios.charges'])
            ->where('property_id', $propertyId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();

        return response()->json($reservations);
    }
}
