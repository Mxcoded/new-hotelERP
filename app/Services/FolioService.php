<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Guest;
use App\Models\Folio; // Assuming the model name is FolioCharge
use App\Models\Folio_charges;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon; // If you're using Str::uuid()
use Illuminate\Support\Facades\Auth; // If you're using auth()
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;
//use Illuminate\Support\Facades\Storage;
//use PDF;

class FolioService
{

    public function createFolioForReservation($reservation)
    {
        // Logic to create a new folio
        // ...
        $folio = Folio::create([
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'date_created' => now(),
            'total_charges' => $reservation->price,
            'total_payments' => $reservation->amount_paid,
            'balance' => $reservation->balance_amount,
        ]);
    }

    public function initializeDailyCharges($checkInId)
    {
        $checkIn = CheckIn::with('reservation')->findOrFail($checkInId);
        $reservation = $checkIn->reservation;
        $roomId = $reservation->room_id;

        // Calculate daily rate based on total reservation cost and number of nights
        $dailyRate = $this->getDailyRate($roomId);
        if ($reservation->price_discount > 0) {
            // Convert the incoming value to a percentage
            $discountPercentage = $reservation->price_discount;
            // Calculate the discount amount
            $discount = ($discountPercentage / 100) * $dailyRate;
            $dailyRate -= $discount; // Subtract the discount from the daily rate
        }

        // Apply the first day's accommodation charge
        $this->addChargeToFolio($reservation->folio_id, 'Daily Accommodation', $dailyRate);

        // Check if there's an early check-in charge applicable
        $this->handleEarlyCheckIn($checkInId);

        // Optionally, apply any existing credit from prepayment
        $this->applyCreditIfAvailable($reservation->folio_id);
    }

    public function updateFolioDetails(Folio $folio, array $validatedData)
    {
        $initialTotalCharges = $folio->total_charges;

        // Update total charges if price changes
        if (isset($validatedData['price'])) {
            $folio->total_charges = $validatedData['price'];
        }

        // Handle additional charges
        if (isset($validatedData['additional_charges']) && $validatedData['additional_charges'] > 0) {
            $folio->total_charges += $validatedData['additional_charges'];
        }

        // Apply discounts if any
        if (isset($validatedData['discount']) && $validatedData['discount'] > 0) {
            $folio->total_charges -= min($validatedData['discount'], $initialTotalCharges);
        }

        // Ensure total charges do not go negative
        $folio->total_charges = max($folio->total_charges, 0);

        // Recalculate the balance
        $folio->balance = $folio->total_charges - $folio->total_payments;
        $folio->save();
    }

    public function recordPayment($folioId, $guestId, $propertyId, $amount, $method)
    {
        $payment = new Payment([
            'folio_id' => $folioId,
            'guest_id' => $guestId,
            'property_id' => $propertyId,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'transaction_id' => Str::uuid(),
            'status' => 'completed',
            'notes' => 'Payment received',
            'processed_by' => Auth::id(), // Assuming you're using Laravel's authentication
        ]);
        $payment->save();

        $this->updateFolioTotalPayments($folioId);
        // Update guest's total balance
        $this->updateGuestBalance($guestId);
    }

    private function updateFolioTotalPayments($folioId)
    {
        $folio = Folio::findOrFail($folioId);
        $folio->total_payments = Payment::where('folio_id', $folioId)->sum('amount');
        $folio->balance = $folio->total_charges - $folio->total_payments;
        $folio->save();
    }

    // Method to be called by a daily scheduled task
    public function applyDailyCharges()
    {
        $activeReservations = Reservation::where('status', 'checked-in')->get();

        foreach ($activeReservations as $reservation) {
            // Retrieve check-in and check-out dates from the reservation
            $checkInDate = $reservation->check_in_date;
            $checkOutDate = $reservation->check_out_date;
            $roomId = $reservation->room_id;

            // Calculate total nights using the calculateTotalNights function
            $totalNights = $this->calculateTotalNights($checkInDate, $checkOutDate);
            $dailyRate = $this->getDailyRate($roomId);

            // Ensure there are nights to calculate charges for
            if ($totalNights > 0) {
                // Calculate daily rate based on total reservation cost and number of nights
                //$dailyRate = $dailyRate / $totalNights;
                if ($reservation->price_discount > 0) {
                    // Convert the incoming value to a percentage
                    $discountPercentage = $reservation->price_discount;
                    // Calculate the discount amount
                    $discount = ($discountPercentage / 100) * $dailyRate;
                    $dailyRate -= $discount; // Subtract the discount from the daily rate
                }
                // Apply the daily charge
                $this->addChargeToFolio($reservation->folio_id, 'Daily Accommodation', $dailyRate);

                // Check and apply any available credit
                //  $this->applyCreditIfAvailable($reservation->folio_id);
            }
        }
    }

    // ... existing methods ...

    /**
     * Calculate the total number of nights based on check-in and check-out dates.
     *
     * @param string $checkInDate Check-in date in 'Y-m-d' format.
     * @param string $checkOutDate Check-out date in 'Y-m-d' format.
     * @return int Total number of nights.
     */
    public function calculateTotalNights($checkInDate, $checkOutDate)
    {
        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        return $checkOut->diffInDays($checkIn);
    }

    // Add a charge to a folio
    public function addChargeToFolio($folioId, $description, $amount)
    {
        $folioCharge = new Folio_charges([
            'folio_id' => $folioId,
            'description' => $description,
            'amount' => $amount,
            'date_incurred' => now(),
            'charge_type' => $description,
        ]);
        $folioCharge->save();

        // Update folio's total charges
        $this->updateFolioTotalCharges($folioId);

        // Update guest's total balance
        $guestId = Folio::find($folioId)->guest_id;
        $this->updateGuestBalance($guestId);
    }

    // Update folio total charges
    public function updateFolioTotalCharges($folioId)
    {
        $folio = Folio::findOrFail($folioId);
        $folio->total_charges = Folio_charges::where('folio_id', $folioId)->sum('amount');
        $folio->balance = $folio->total_charges - $folio->total_payments;
        $folio->save();
    }

    // Apply available credit to the folio
    public function applyCreditIfAvailable($folioId)
    {
        $folio = Folio::findOrFail($folioId);

        if ($folio->balance < 0) { // Indicates credit
            $creditAmount = abs($folio->balance);
            $folio->total_payments += $creditAmount;
            $folio->balance = 0; // Reset balance as credit is used
            $folio->save();
        }
    }

    // Method to handle late checkout charges
    public function applyLateCheckoutCharges($reservationId, $hourlyLateCheckoutFee)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $checkoutTime = Carbon::parse($reservation->check_out_date);

        if (Carbon::now()->gt($checkoutTime)) {
            // Calculate hours past the checkout time
            $hoursLate = Carbon::now()->diffInHours($checkoutTime);
            $lateFee = $hoursLate * $hourlyLateCheckoutFee;

            // Apply the late checkout fee
            $this->addChargeToFolio($reservation->folio_id, 'Late Checkout Fee', $lateFee);
        }
    }

    public function handleEarlyCheckIn($checkInId)
    {
        $checkIn = CheckIn::with('reservation.folio')->findOrFail($checkInId);

        if (!$checkIn->reservation || !$checkIn->reservation->folio_id) {
            // Handle the case where folio is not set - perhaps log this issue and return
            Log::error("Folio not found for CheckIn ID: {$checkInId}");
            return;
        }

        $standardCheckInTime = Carbon::createFromTime(15, 0, 0); // 3 PM
        $checkInTime = Carbon::parse($checkIn->check_in_time);

        if ($checkInTime->lt($standardCheckInTime)) {
            // Apply the early check-in fee
            $this->addChargeToFolio($checkIn->reservation->folio_id, 'Early Check-In Fee', 10000);
        }
    }


    public function updateGuestBalance($guestId)
    {
        $guest = Guest::findOrFail($guestId);

        // Calculate total charges
        $totalCharges = Folio_charges::whereHas('folio', function ($query) use ($guestId) {
            $query->where('guest_id', $guestId);
        })->sum('amount');

        // Calculate total payments
        $totalPayments = Payment::where('guest_id', $guestId)->sum('amount');

        // Update total balance
        $guest->total_balance = $totalCharges - $totalPayments;
        $guest->save();

        return $guest->total_balance;
    }


    /**
     * Initializes a folio for a direct check-in.
     *
     * @param CheckIn     $checkIn     the CheckIn model instance
     * @param array       $data        the validated data from the request
     * @param Reservation $reservation the Reservation model instance
     */
    public function initializeFolioForDirectCheckIn($checkIn, $data, $reservation)
    {
        // Calculate initial charges based on reservation details
        $initialCharges = $this->calculateInitialCharges($reservation);

        // Create or update the folio
        $folio = Folio::updateOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'guest_id' => $reservation->guest_id,
                'check_in_id' => $checkIn->id,
                'date_created' => now(),
                'total_charges' => $initialCharges,
                'total_payments' => $data['advance_payment'] ?? 0,
                'balance' => $initialCharges - ($data['advance_payment'] ?? 0),
                'status' => 'Open', // or other relevant status
            ]
        );

        // If there is an advance payment, record it
        if (!empty($data['advance_payment'])) {
            $this->recordPayment(
                $folio->id,
                $reservation->guest_id,
                $reservation->property_id,
                $data['advance_payment'],
                $data['payment_method'] ?? 'unknown'
            );
        }

        // Additional logic for initializing daily charges, handling early check-ins, etc.
        // ...
    }

    private function calculateInitialCharges(Reservation $reservation)
    {
        // Fetch the room information
        $room = Room::find($reservation->room_id);

        // Check if the room has a specific base price set
        $basePrice = $room->base_price ?? $room->roomType->base_price;

        // Calculate the total charges based on the base price and the duration of the stay
        $stayDuration = Carbon::parse($reservation->check_in_date)->diffInDays(Carbon::parse($reservation->check_out_date));
        $totalCharges = $basePrice * $stayDuration;

        return $totalCharges; // Replace with actual calculation logic
    }

    /**
     * Finalize folio and generate invoice for a reservation.
     *
     * @return Invoice
     *
     * @throws Exception
     */
    public function finalizeFolioAndGenerateInvoice(Reservation $reservation)
    {
        if (!$reservation->folio) {
            throw new Exception('No folio associated with the reservation.');
        }

        // Ensure all charges are accounted for and finalize the folio
        $this->finalizeFolio($reservation->folio);

        // Generate an invoice based on the finalized folio
        return $this->generateInvoice($reservation->id);
    }

    /**
     * Finalize the folio by calculating the final balance.
     */
    private function finalizeFolio(Folio $folio)
    {
        // Calculate the final balance or perform any other necessary finalization logic
        $folio->update([
            'status' => 'Closed',
            // Other finalization details can be updated here
        ]);
    }

    public function generateInvoice($reservationId)
    {
        DB::beginTransaction();
        try {
            // Load reservation details with related data
            $reservation = Reservation::with(['guest', 'property', 'room', 'folios', 'folios.charges', 'folios.payments'])
                ->findOrFail($reservationId);

            // Check if folios exist for this reservation
            if ($reservation->folios->isEmpty()) {
                throw new \Exception('No folios found for this reservation.');
            }

            $folio = $reservation->folios->first();
            $invoiceNumber = 'BP' . $reservation->property_id . '_' . Str::padLeft(Invoice::max('id') + 1, 7, '0');

            $totalCharges = $folio->charges->sum('amount');
            $totalPayments = $folio->payments->sum('amount');
            $balance = $totalCharges - $totalPayments;

            // Create the Invoice record
            $invoice = Invoice::create([
                'guest_id' => $reservation->guest_id,
                'property_id' => $reservation->property_id,
                'reservation_id' => $reservation->id,
                'total_amount' => $balance,
                'issue_date' => now(),
                'status' => $balance > 0 ? 'unpaid' : 'paid',
                'type' => 'standard',
                'description' => 'Invoice for reservation #' . $reservation->id,
                'invoice_number' => $invoiceNumber,
            ]);

            $property = $reservation->property;
            $guest = $reservation->guest;
            $charges = $folio->charges;
            $totalAmount = $invoice->total_amount;
            $invoice = $invoice;


            // Send email with PDF attachment

            // $this->sendInvoiceEmail($invoice);

            DB::commit();

            // return $invoice;
            // Return all the data in an array
            return [
                'reservation' => $reservation->toArray(),
                'folio' => $folio->toArray(),
                'invoice' => $invoice->toArray(),
                'property' => $property->toArray(),
                'guest' => $guest->toArray(),
                'invoice' => $invoice->toArray(),
                'totalCharges' => $totalCharges,
                'totalPayments' => $totalPayments,
                'balance' => $balance,
                // Add other necessary data here
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function sendInvoiceEmail($invoice)
    {
        // Ensure $invoice is available and is an object
        if (!$invoice || !is_object($invoice)) {
            Log::error('Invoice object not available for email sending.');
            return;
        }

        try {
            $guestEmail = $invoice->guest->email;
            if ($guestEmail) {
                Mail::send('emails.invoice', ['invoice' => $invoice], function ($message) use ($guestEmail, $invoice) {
                    // Include $invoice in the use statement
                    $message->to($guestEmail)
                        ->subject('Your Invoice - ' . $invoice->invoice_number);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error in sendInvoiceEmail: ' . $e->getMessage());
            throw $e;
        }
    }


    // Other methods as needed...
    /**
     * Recalculates charges for an extended stay.
     *
     * @param int $folioId ID of the folio to update.
     * @param string $newCheckoutDate New check-out date.
     * @return Folio Updated folio model.
     */
    public function recalculateChargesForExtendedStay($folioId, $newCheckoutDate)
    {
        $folio = Folio::with('reservation')->findOrFail($folioId);
        $reservation = $folio->reservation;

        // Calculate the original and new duration of the stay
        $originalDuration = Carbon::parse($reservation->check_in_date)->diffInDays(Carbon::parse($reservation->check_out_date));
        $newDuration = Carbon::parse($reservation->check_in_date)->diffInDays(Carbon::parse($newCheckoutDate));

        // Calculate the difference in days
        $additionalDays = $newDuration - $originalDuration;

        if ($additionalDays > 0) {
            // Fetch the room or room type's daily rate
            $dailyRate = $this->getDailyRate($reservation->room_id);

            // Calculate additional charges
            $additionalCharges = $additionalDays * $dailyRate;

            // Update folio's total charges
            $folio->total_charges += $additionalCharges;
            $folio->save();

            // Optionally, add a record of this charge to the folio charges table
            $this->addChargeToFolio($folioId, 'Extended Stay', $additionalCharges);
        }

        return $folio;
    }
    /**
     * Fetch the daily rate for a room or room type.
     *
     * @param int $roomId ID of the room.
     * @return float Daily rate of the room.
     */
    public function getDailyRate($roomId)
    {
        $room = Room::with('roomType')->find($roomId);

        if (!$room) {
            throw new Exception("Room with ID $roomId not found.");
        }

        // Use room's base_price if available; otherwise, fall back to the room type's base_price.
        // Ensure that the Room model is correctly related to a RoomType model (or similar) and both have a base_price field.
        $dailyRate = $room->base_price ?? $room->roomType->base_price;

        if (!$dailyRate) {
            throw new Exception("Daily rate not found for room ID $roomId.");
        }

        return $dailyRate;
    }
}
