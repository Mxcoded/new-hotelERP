<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Folio;
use App\Models\Room; // Add the missing import for Room
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Add the missing import for Str
use Exception;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        // Improved validation with custom messages
        $validator = Validator::make($request->all(), [
            'reservation_id' => 'sometimes|exists:reservations,id',
            'guest_id' => 'required_without:reservation_id|exists:guests,id',
            'property_id' => 'required|exists:properties,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            // Add more fields if necessary
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();

        try {
            DB::beginTransaction();

            $folio = $this->getOrCreateFolio($request);

            // Create and save the payment
            $payment = new Payment([
                'reservation_id' => $validatedData['reservation_id'] ?? null,
                'folio_id' => $folio->id,
                'guest_id' => $validatedData['guest_id'],
                'property_id' => $validatedData['property_id'],
                'amount' => $validatedData['amount'],
                'payment_method' => $validatedData['payment_method'],
                'payment_date' => now(), // Assuming current date as payment date
                'transaction_id' => Str::uuid(), // Generate a unique transaction ID
                'status' => 'completed', // Assuming immediate completion, adjust as needed
                'notes' => 'Payment received', // Optional notes
                'processed_by' => auth()->user()->id, // Assuming authenticated user
            ]);
            $payment->save();

            // Update folio balance
            $this->updateFolioBalance($folio, $validatedData['amount']);

            DB::commit();
            return response()->json(['message' => 'Payment processed successfully', 'payment' => $payment], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    private function getOrCreateFolio(Request $request)
    {
        if ($request->filled('reservation_id')) {
            return Folio::where('reservation_id', $request->input('reservation_id'))->firstOrFail();
        } else {
            // Assuming that the necessary data for creating a new reservation is part of the request.
            // This includes guest_id, property_id, room_type_id, check_in_date, and check_out_date.
            $reservation = $this->createNewReservation($request);
            return $this->createNewFolio($request, $reservation);
        }
    }

    private function updateFolioBalance(Folio $folio, $paymentAmount)
    {
        $folio->total_payments += $paymentAmount;
        $folio->balance = $folio->total_charges - $folio->total_payments;
        $folio->save();

        // Handle overpayment or underpayment scenarios
        if ($folio->balance < 0) {
            // Overpayment logic
        } elseif ($folio->balance > 0) {
            // Underpayment logic
        }
    }

    private function createNewReservation(Request $request)
    {
        // Validation of request data
        $validatedData = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'property_id' => 'required|exists:properties,id',
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
        ]);

        $availableRoom = Room::where('room_type_id', $validatedData['room_type_id'])
            ->where('status', 'available')
            ->where('property_id', $validatedData['property_id'])
            ->first();

        if (!$availableRoom) {
            throw new Exception('No available room found for the specified criteria.');
        }

        $reservation = Reservation::create([
            'guest_id' => $validatedData['guest_id'],
            'room_id' => $availableRoom->id,
            'reservation_date' => now(),
            'check_in_date' => $validatedData['check_in_date'],
            'check_out_date' => $validatedData['check_out_date'],
            'status' => 'confirmed',
            'property_id' => $validatedData['property_id'],
        ]);

        $availableRoom->update(['status' => 'occupied']);

        return $reservation;
    }


    /**
     * Create a new folio for a reservation.
     */
    private function createNewFolio(Request $request, $reservation)
    {
        $stayDuration = $reservation->check_in_date->diffInDays($reservation->check_out_date);
        $totalCharges = $reservation->room->base_price * $stayDuration;

        $folio = Folio::create([
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'date_created' => now(),
            'total_charges' => $totalCharges,
            'status' => 'Open',
        ]);

        return $folio;
    }


    // Retrieve payment history for a guest
    public function history($guestId)
    {
        // Implement payment history retrieval logic here
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            // Include necessary validation rules
            'amount' => 'required|numeric',
            'method' => 'required|string',
            'status' => 'required|string',
            'folio_id' => 'required|exists:folios,id',
            'reservation_id' => 'sometimes|exists:reservations,id',
            'guest_id' => 'sometimes|exists:guests,id',
        ]);
        $payment = Payment::findOrFail($id);
        $oldAmount = $payment->amount;
        $payment->update($validatedData);

        // Additional logic if needed
        // Recalculate folio balance based on the difference in payment amounts
        $amountDifference = $validatedData['amount'] - $oldAmount;
        $folio = $payment->folio;
        $this->updateFolioBalance($folio, $amountDifference);

        return response()->json($payment, 200);
    }
    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->delete();
        // Additional cleanup logic if needed

        return response()->json(null, 204);
    }

    public function listByFolio($folioId)
    {
        $payments = Payment::where('folio_id', $folioId)->get();
        return response()->json($payments);
    }

    // private function handlePaymentBalance($payment)
    // {
    //     $folio = Folio::find($payment->folio_id);
    //     if (!$folio) {
    //         return;
    //     }

    //     $totalCharges = $folio->total_charges;
    //     $totalPayments = $folio->payments()->sum('amount');
    //     $balance = $totalPayments - $totalCharges;

    //     if ($balance > 0) {
    //         // Handle overpayment
    //         // Possibly issue a credit note or refund
    //     } elseif ($balance < 0) {
    //         // Handle underpayment
    //         // Notify the guest or take necessary actions
    //     }

    //     // Update folio balance
    //     $folio->update(['balance' => $balance]);
    // }

    public function listByGuest($guestId)
    {
        $payments = Payment::where('guest_id', $guestId)->get();
        return response()->json($payments);
    }
    public function processDirectCheckInPayment(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'method' => 'required|string',
            // Assume 'guest_id' and 'room_id' are provided for direct check-ins
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
        ]);

        try {
            DB::beginTransaction();

            // Create or find a folio for the guest and room
            $folio = Folio::firstOrCreate(
                ['guest_id' => $validatedData['guest_id'], 'room_id' => $validatedData['room_id']],
                ['total_charges' => 0, 'balance' => 0]
            );

            // Create the payment record
            $payment = Payment::create(array_merge($validatedData, ['folio_id' => $folio->id]));

            // Update folio with new payment
            $folio->total_payments += $validatedData['amount'];
            $folio->balance = $folio->total_charges - $folio->total_payments;
            $folio->save();

            DB::commit();
            return response()->json(['message' => 'Payment processed successfully', 'payment' => $payment], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Payment processing failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function processRefund(Request $request, $paymentId)
    {
        $validatedData = $request->validate([
            'refund_amount' => 'required|numeric|lte:amount',
        ]);

        $payment = Payment::findOrFail($paymentId);

        // Logic to process the refund
        // ...

        // Update payment record with refund details
        $payment->refund_amount = $validatedData['refund_amount'];
        $payment->status = 'refunded';
        $payment->save();

        return response()->json(['message' => 'Refund processed successfully', 'payment' => $payment], 200);
    }

    public function recordPayment(Request $request)
    {
        $validatedData = $request->validate([
            'folio_id' => 'required|exists:folios,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string', // e.g., cash, credit card
            // Additional fields like 'transaction_id' might be needed depending on your requirement
        ]);

        try {
            DB::beginTransaction();

            // Retrieve the folio
            $folio = Folio::findOrFail($validatedData['folio_id']);

            // Create the payment record
            $payment = new Payment([
                'folio_id' => $folio->id,
                'guest_id' => $validatedData['guest_id'],
                'property_id' => $validatedData['property_id'],
                'amount' => $validatedData['amount'],
                'payment_method' => $validatedData['payment_method'],
                'payment_date' => now(), // Assuming current date as payment date
                'transaction_id' => Str::uuid(), // Generate a unique transaction ID
                'status' => 'completed', // Assuming immediate completion, adjust as needed
                'notes' => 'Payment received2', // Optional notes
                'processed_by' => auth()->user()->id, // Assuming authenticated user
            ]);
            $payment->save();

            // Update folio's total payments and balance
            $folio->total_payments += $validatedData['amount'];
            $folio->balance = $folio->total_charges - $folio->total_payments;
            $folio->save();

            DB::commit();
            return response()->json(['success' => true, 'payment' => $payment], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Other necessary functions...
}
