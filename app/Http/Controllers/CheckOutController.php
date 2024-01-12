<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Reservation;
use App\Services\FolioService;
// Import the FolioService
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use illuminate\Support\Facades\Log;

class CheckOutController extends Controller {
    protected $folioService;

    public function __construct( FolioService $folioService ) {
        $this->folioService = $folioService;
    }

    // List all check-outs

    public function index() {
        $checkOuts = CheckOut::with( [ 'reservation.guest', 'reservation.room', 'user' ] )->get();

        return response()->json( $checkOuts );
    }

    // Show a single check-out record

    public function show( $id ) {
        $checkOut = CheckOut::with( [ 'reservation.guest', 'reservation.room', 'user' ] )->find( $id );

        return response()->json( $checkOut );
    }

    // Record a new check-out

    public function store( Request $request ) {
        $validatedData = $request->validate( [
            'check_in_id' => 'required|exists:check_ins,id',
            'notes' => 'nullable|string',
            // Add validations for new fields
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'total_bill' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'outstanding_balance' => 'required|numeric',
            // 'payment_status' => 'required|string|in:paid,unpaid,partial',
            'late_check_out' => 'nullable|boolean',
        ] );

        try {
            DB::beginTransaction();

            $checkIn = CheckIn::with( [ 'reservation', 'room' ] )->findOrFail( $validatedData[ 'check_in_id' ] );
            if ( $checkIn->reservation && $checkIn->reservation->status != 'checked-in' ) {
                throw new \Exception( 'Reservation is not in a checked-in state' );
            }

            $reservation = $checkIn->reservation;

            // Define standard checkout time ( e.g., 11 AM on the checkout date )
            $standardCheckoutTime = Carbon::parse( $reservation->check_out_date )->setTime( 12, 0 );

            // Check if actual checkout is late
            $isLateCheckout = now()->greaterThan( $standardCheckoutTime );

            $totalBill = $validatedData[ 'total_bill' ];
            $amountPaid = $validatedData[ 'amount_paid' ];
            $outstandingBalance = $validatedData[ 'outstanding_balance' ];
            // $totalBill - $amountPaid;

            $paymentStatus = '';
            if ( $outstandingBalance == 0 ) {
                $paymentStatus = 'Paid';
            } elseif ( $outstandingBalance < $totalBill ) {
                $paymentStatus = 'partially_paid';
            } else {
                $paymentStatus = 'Unpaid';
            }

            // Temporary debugging line
            \Log::info( 'Payment Status: ' . $paymentStatus );
            // Record the check-out
            $checkOut = CheckOut::create( [
                'check_in_id' => $checkIn->id,
                'reservation_id' => $checkIn->reservation_id,
                'guest_id' => $validatedData[ 'guest_id' ],
                'room_id' => $validatedData[ 'room_id' ],
                'check_out_time' => now(),
                'total_bill' => $totalBill,
                'amount_paid' => $amountPaid,
                'outstanding_balance' => $outstandingBalance,
                'payment_status' => $paymentStatus,
                'notes' => $validatedData[ 'notes' ] ?? '',
                'late_check_out' => $isLateCheckout,
            ] );

            // Update reservation status
            $checkIn->reservation->update( [ 'status' => 'checked-out' ] );

            // Update room status
            $checkIn->room->update( [ 'status' => 'available' ] );

            // Finalize folio and generate invoice
            $invoice = $this->folioService->finalizeFolioAndGenerateInvoice( $checkIn->reservation );

            DB::commit();

            return response()->json( [
                'message' => 'Check-out successful',
                'checkOut' => $checkOut,
                'invoice' => $invoice, // include invoice details in response if needed
            ], 201 );
        } catch ( \Exception $e ) {
            DB::rollBack();

            return response()->json( [ 'message' => 'Check-out process failed', 'error' => $e->getMessage() ], 500 );
        }
    }

    // Update a check-out record ( e.g., handle late check-outs, billing adjustments )

    public function update( Request $request, $id ) {
        $checkOut = CheckOut::findOrFail( $id );
        $checkOut->update( $request->all() );

        // Additional logic for specific check-out scenarios
        return response()->json( $checkOut, 200 );
    }

    // Delete a check-out record ( if necessary )

    public function destroy( $id ) {
        CheckOut::find( $id )->delete();

        // Additional cleanup if required
        return response()->json( null, 204 );
    }

    // Additional methods for specific operations...

    // Example: Finalizing billing and payments

    public function finalizeBilling( $id, Request $request ) {
        // Logic for finalizing the billing process for a check-out
    }

    // List Checkout by property

    public function listByProperty( $propertyId ) {
        $checkout = CheckOut::where( 'property_id', $propertyId )->get();

        return response()->json( $checkout );
    }
}
