<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function createBooking(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'nullable|exists:rooms,id',
            'facility_id' => 'nullable|exists:facilities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:confirmed,cancelled,completed',
            // Additional validation rules as required
        ]);

        // Check for overlapping bookings
        if ($this->hasOverlappingBookings($validatedData)) {
            return response()->json(['message' => 'The requested time slot is already booked'], Response::HTTP_CONFLICT);
        }

        DB::beginTransaction();
        try {
            // Create the booking
            $booking = Booking::create($validatedData);
            DB::commit();

            return response()->json(['message' => 'Booking created successfully', 'booking' => $booking], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create booking', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function hasOverlappingBookings(array $data, $excludeBookingId = null)
    {
        $query = Booking::where('property_id', $data['property_id'])
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    // Check if the new/updated booking's start or end date falls within any existing booking period
                    $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                        ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']]);
                })
                    ->orWhere(function ($q) use ($data) {
                        // Check if any existing booking period falls within the new/updated booking's start or end date
                        $q->where('start_date', '<', $data['start_date'])
                            ->where('end_date', '>', $data['end_date']);
                    });
            });

        // Exclude the current booking from the overlap check (useful during booking updates)
        if ($excludeBookingId) {
            $query->where('id', '<>', $excludeBookingId);
        }

        if (!empty($data['room_id'])) {
            $query->where('room_id', $data['room_id']);
        }

        if (!empty($data['facility_id'])) {
            $query->where('facility_id', $data['facility_id']);
        }

        return $query->exists();
    }


    public function updateBooking(Request $request, $bookingId)
    {
        // Retrieve the existing booking or fail with a 404 error
        $booking = Booking::findOrFail($bookingId);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'property_id' => 'sometimes|required|exists:properties,id',
            'guest_id' => 'sometimes|required|exists:guests,id',
            'room_id' => 'nullable|exists:rooms,id',
            'facility_id' => 'nullable|exists:facilities,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'status' => 'sometimes|required|in:confirmed,cancelled,completed',
            // Additional validation rules as needed
        ]);

        // Check for overlapping bookings if dates or room/facility are being updated
        if ($this->isUpdatingBookingTimes($request, $booking) && $this->hasOverlappingBookings($validatedData, $bookingId)) {
            return response()->json(['message' => 'The updated time slot conflicts with existing bookings'], Response::HTTP_CONFLICT);
        }

        // Handle specific status updates (e.g., cancellations, completions)
        $this->handleStatusUpdate($booking, $validatedData);

        // Update the booking with validated data
        $booking->update($validatedData);

        return response()->json(['message' => 'Booking updated successfully', 'booking' => $booking], Response::HTTP_OK);
    }

    private function isUpdatingBookingTimes(Request $request, Booking $booking)
    {
        return $request->has('start_date') && $request->input('start_date') != $booking->start_date ||
            $request->has('end_date') && $request->input('end_date') != $booking->end_date;
    }

    private function handleStatusUpdate(Booking $booking, array $data)
    {
        // Logic for handling status updates, such as sending notifications for cancellations
        if (isset($data['status']) && $data['status'] != $booking->status) {
            // Implement status-specific actions
        }
    }

    public function cancelBooking(Request $request, $bookingId)
    {
        // Find the booking or return a 404 error if not found
        $booking = Booking::findOrFail($bookingId);

        // Check if the booking is eligible for cancellation
        if (!$this->canBeCancelled($booking)) {
            return response()->json(['message' => 'Booking cannot be cancelled as per our cancellation policy.'], Response::HTTP_FORBIDDEN);
        }

        // Handle potential fees or refunds for cancellation
        $cancellationFee = $this->calculateCancellationFee($booking);

        // Update the booking status to 'cancelled'
        $booking->status = 'cancelled';
        $booking->save();

        // Record cancellation reason and who performed it
        $cancellationReason = $request->input('reason', 'No specific reason provided');
        $cancelledBy = auth()->user()->name; // Assuming the user is authenticated

        $this->recordCancellationAudit($booking, $cancellationReason, $cancelledBy);

        // Notify the guest and relevant hotel staff about the cancellation
        $this->sendCancellationNotifications($booking);

        // Additional actions such as updating room or facility availability
        $this->updateAvailabilityPostCancellation($booking);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'cancellation_fee' => $cancellationFee
        ], Response::HTTP_OK);
    }

    private function recordCancellationAudit(Booking $booking, $reason, $cancelledBy)
    {
        AuditLog::create([
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'action' => 'Cancelled',
            'description' => $reason,
            'performed_by' => $cancelledBy,
        ]);
    }

    public function getBookingDetails($bookingId)
    {
        // Retrieve and return booking details
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
