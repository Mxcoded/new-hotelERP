<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\Folio;
use App\Models\Guest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use Exception;



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
        // Validate the request data
        $validatedData = $request->validate([
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
            'balance_amount' => 'required|numeric',
            'special_requests' => 'nullable|string',
            'cancellation_policy_id' => 'required|exists:cancellation_policies,id',
            'property_id' => 'required|exists:properties,id',
        ]);

        try {
            DB::beginTransaction();

            // Find an available room based on room type, dates, and property
            $availableRoom = Room::where([
                ['status', 'available'], // Here we use 'status' instead of 'is_available'
                ['property_id', $validatedData['property_id']],
            ])->first();

            if (!$availableRoom) {
                return response()->json(['message' => 'No available rooms for the selected date or room type'], 404);
            }
            // Generate a unique reservation number with prefix 'BP'
            $reservationNumber = 'BP' . strtoupper(Str::random(8));

            // Create the reservation
            $reservation = Reservation::create(array_merge($validatedData, ['reservation_number' => $reservationNumber]));

            // Assuming you receive an array of guest IDs with the request
            $guestIds = $request->input('guest_id', []); // Ensure this is an array

            // Attach guests to the reservation
            $reservation->guests()->attach($guestIds);

            // Check if there's an initial payment and handle it
            if ($validatedData['amount_paid'] > 0) {
                // Here, integrate with the PaymentController or related logic
                // to handle the payment and folio creation

                // Create a new folio for the reservation
                $folio = Folio::create([
                    'reservation_id' => $reservation->id,
                    'guest_id' => $reservation->guest_id,
                    'date_created' => now(),
                    'total_charges' => $reservation->price,
                    'total_payments' => $reservation->amount_paid,
                    'balance' => $reservation->balance_amount,
                ]);

                // Create the initial payment record
                Payment::create([
                    'reservation_id' => $reservation->id ?? null,
                    'folio_id' => $folio->id,
                    'guest_id' => $reservation->guest_id,
                    'property_id' => $validatedData['property_id'],
                    'amount' => $validatedData['amount_paid'],
                    'payment_method' => $validatedData['payment_method'],
                    'payment_date' => now(), // Assuming current date as payment date
                    'transaction_id' => Str::uuid(), // Generate a unique transaction ID
                    'status' => 'completed', // Assuming immediate completion, adjust as needed
                    'notes' => 'Initial reservation deposit',
                    'processed_by' => auth()->user()->id, // Assuming authenticated user
                ]);

                // Update reservation with the 'folio_id'
                $reservation->folio_id = $folio->id;
                $reservation->save();
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
            // Find the reservation and its associated folio
            $reservation = Reservation::with(['room', 'folio'])->findOrFail($id);

            // Check if the reservation is already checked-in and avoid conflicting updates
            if ($reservation->status === 'checked-in') {
                throw new Exception('Cannot update a reservation that is already checked-in.');
            }

            // Validate the request data
            $validatedData = $request->validate([
                'guest_id' => 'sometimes|exists:guests,id',
                'room_id' => 'sometimes|exists:rooms,id',
                'check_in_date' => 'sometimes|date',
                'check_out_date' => 'sometimes|date|after_or_equal:check_in_date',
                'number_of_guests' => 'sometimes|integer',
                'price' => 'sometimes|numeric',
                'status' => 'sometimes|string',
                'payment_method' => 'sometimes|string',
                'payment_status' => 'sometimes|string',
                'amount_paid' => 'sometimes|numeric',
                // Include other fields that might need validation
            ]);



            // Update the reservation details
            $reservation->update($validatedData);

            // Update room availability if room_id is changed
            if (isset($validatedData['room_id']) && $validatedData['room_id'] != $reservation->room_id) {
                Room::where('id', $reservation->room_id)
                    ->update(['status' => 'available']); // Previous room

                Room::where('id', $validatedData['room_id'])
                    ->update(['status' => 'reserved']); // New room
            }

            // Handle folio updates
            if ($folio = $reservation->folio) {
                $folio->total_charges = $validatedData['price'] ?? $folio->total_charges;
                $folio->total_payments = $validatedData['amount_paid'] ?? $folio->total_payments;
                $folio->balance = ($folio->total_charges ?? 0) - ($folio->total_payments ?? 0);
                $folio->save();

                // Update the reservation's balance_amount
                $reservation->balance_amount = $folio->balance;
                $reservation->save();
            }
            // Check if there's additional payment or refund needed
            if ($request->has('additional_payment')) {
                Payment::create([
                    'folio_id' => $reservation->folio_id,
                    'guest_id' => $reservation->guest_id,
                    'amount' => $request->additional_payment,
                    'method' => $request->payment_method, // Ensure this is included in validation
                    'status' => 'completed', // or 'pending' based on your payment process
                    'notes' => 'Additional payment for reservation update'
                ]);
            }

            // Additional logic for handling changes in reservation details

            DB::commit();
            return response()->json($reservation, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
}
