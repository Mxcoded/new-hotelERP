<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Booking;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class FacilityController extends Controller
{
    // Detailed configuration of a specific facility
    public function configureFacility(Request $request, $facilityId)
    {
       // Find the facility or fail with a 404 response
    $facility = Facility::findOrFail($facilityId);

    // Validate the incoming request data
    $validatedData = $request->validate([
        'name' => 'sometimes|string|max:255',
        'type' => 'sometimes|string|max:255', // e.g., 'hall', 'conference room'
        'description' => 'sometimes|string',
        'capacity' => 'sometimes|integer',
        'available' => 'sometimes|boolean',
        // Add other fields as necessary
    ]);

    // Update the facility with validated data
    $facility->update($validatedData);

    // Respond with the updated facility data
    return response()->json($facility, 200);
    }

    // Booking a facility
    public function bookFacility(Request $request, $facilityId)
    {
                // Validate the incoming request data
            $validatedData = $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'user_id' => 'required|exists:users,id', // Assuming bookings are tied to a user
                // Add other booking related fields as necessary
            ]);

            // Find the facility or fail with a 404 response
            $facility = Facility::findOrFail($facilityId);

            // Check if the facility is available for the requested dates
            $isAvailable = $this->checkFacilityAvailability($facility, $validatedData['start_date'], $validatedData['end_date']);

            if (!$isAvailable) {
                return response()->json(['message' => 'Facility is not available for the requested dates'], 403);
            }

            // Create the booking record
            $booking = $facility->bookings()->create($validatedData);

            // Respond with booking details
            return response()->json($booking, 201);
        //return response()->json(['message' => 'Facility booked successfully'], 200);

    }

    /**
     * Check if the facility is available for the given dates.
     */
    private function checkFacilityAvailability($facility, $startDate, $endDate, $startTime = null, $endTime = null, $bufferTimeInMinutes = 0)
    {
        // Convert dates and times to DateTime objects for comparison
        $startDateTime = new \DateTime("$startDate $startTime");
        $endDateTime = new \DateTime("$endDate $endTime");

        // Adjust for buffer time
        if ($bufferTimeInMinutes > 0) {
            $startDateTime->modify("-$bufferTimeInMinutes minutes");
            $endDateTime->modify("+$bufferTimeInMinutes minutes");
        }

        // Check for overlapping bookings
        $overlappingBookings = $facility->bookings()
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                // Check for bookings that overlap with the requested dates and times
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    $q->whereBetween('start_date', [$startDateTime, $endDateTime])
                    ->orWhereBetween('end_date', [$startDateTime, $endDateTime]);
                })->orWhere(function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_date', '<', $startDateTime)
                    ->where('end_date', '>', $endDateTime);
                });
            })->exists();

        // If there are overlapping bookings, the facility is not available
        return !$overlappingBookings;
    }


    // Cancelling a facility booking
    public function cancelBooking($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        if (!$this->canCancelBooking($booking)) {
            return response()->json(['message' => 'Cancellation period has passed. Booking cannot be canceled.'], 403);
        }

        // Perform cancellation logic
        $booking->update(['status' => 'canceled']);

        // Update facility availability
        $this->updateFacilityAvailability($booking->facility_id);

        // Process refund if applicable
        $this->processRefund($booking);

        // Send notification to the customer and staff
        $this->sendCancellationNotification($booking);

        return response()->json(['message' => 'Booking canceled successfully'], 200);
    }
    private function updateFacilityAvailability($facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        // Logic to update the facility's availability
        // For simplicity, let's mark it as available
        $facility->update(['is_available' => true]);
    }

    private function processRefund(Booking $booking)
    {
        // Refund processing logic
        // E.g., interacting with a payment gateway
        // Mock implementation for demonstration
        if ($booking->isPaid()) {
            // Logic to initiate the refund process
            // ...
        }
    }

    private function sendCancellationNotification(Booking $booking)
    {
        // Notification logic
        // E.g., sending an email or app notification
        $booking->user->notify(new BookingCancellationNotification($booking));
        // Also notify internal staff or management if needed
        // ...
    }
    /**
     * Checks whether the given booking can be canceled based on the hotel's cancellation policy.
     */
    private function canCancelBooking(Booking $booking)
    {
        // Cancellation is allowed up to 48 hours before the booking start date
        $cancellationDeadline = (new \DateTime($booking->start_date))->modify('-48 hours');
        return now() <= $cancellationDeadline;
    }

    // Checking availability of a facility
   // public function checkAvailability($facilityId)
    public function checkAvailability($facilityId)
    {
        $facility = Facility::with(['bookings', 'maintenanceSchedule'])->findOrFail($facilityId);

        // Determine facility's availability
        $availability = $this->determineFacilityAvailability($facility);

        return response()->json([
            'facility_id' => $facilityId,
            'is_available' => $availability,
        ], Response::HTTP_OK);
    }

    /**
     * Determines the availability of the facility by checking bookings, maintenance schedules, etc.
     */
    private function determineFacilityAvailability(Facility $facility)
    {
        // Check for overlapping bookings
        $hasActiveBookings = $facility->bookings->any(function ($booking) {
            return $booking->status === 'booked' && $booking->end_date > now();
        });

        if ($hasActiveBookings) {
            return false; // Not available if there are active bookings
        }

        // Check for maintenance or other conditions
        $isUnderMaintenance = $facility->maintenanceSchedule->contains(function ($maintenance) {
            return $maintenance->start_date <= now() && $maintenance->end_date >= now();
        });

        if ($isUnderMaintenance) {
            return false; // Not available if under maintenance
        }

        // Add other checks if necessary, like special events, etc.
        // ...

        return true; // Available if none of the above conditions are met
    }

    // Updating availability status of a facility
    public function updateAvailability(Request $request, $facilityId)
    {
        // Authorization check (assuming a method 'canUpdateFacility' for simplicity)
        if (!Auth::user()->canUpdateFacility()) {
            return response()->json(['message' => 'Unauthorized to update facility availability'], Response::HTTP_FORBIDDEN);
        }

        $facility = Facility::findOrFail($facilityId);

        // Validate the incoming request
        $validatedData = $request->validate([
            'is_available' => 'required|boolean',
        ]);

        try {
            // Update the facility's availability status
            $facility->update($validatedData);

            // Send notifications if necessary (to staff, management, etc.)
            $this->notifyAvailabilityChange($facility);

            // Log the update for auditing purposes
            Log::info("Facility availability updated by user: " . Auth::id(), ['facility_id' => $facilityId, 'status' => $validatedData['is_available']]);

            return response()->json([
                'message' => 'Facility availability updated successfully',
                'facility' => $facility
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any exceptions during the update process
            Log::error('Error updating facility availability: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update facility availability'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Notify relevant parties about the availability change.
     */
    private function notifyAvailabilityChange(Facility $facility)
    {
        // Notification logic
        // This might involve sending emails, SMS, or system notifications
        // Example: $facility->notify(new FacilityAvailabilityChangedNotification($facility));
    }

    // Generating a report on facility usage
    public function generateUsageReport($facilityId)
    {
        $facility = Facility::with(['bookings', 'events'])->findOrFail($facilityId);

        // Aggregate data for the report
        $reportData = $this->aggregateFacilityUsageData($facility);

        return response()->json([
            'facility_id' => $facilityId,
            'usage_report' => $reportData
        ], Response::HTTP_OK);
    }

    private function aggregateFacilityUsageData(Facility $facility)
    {
        $numberOfBookings = $facility->bookings->count();
        $peakUsage = $this->calculatePeakUsageTimes($facility->bookings);
        $typesOfEvents = $this->calculateTypesOfEvents($facility->events);
        $averageBookingDuration = $this->calculateAverageBookingDuration($facility->bookings);

        return [
            'number_of_bookings' => $numberOfBookings,
            'peak_usage_times' => $peakUsage,
            'types_of_events' => $typesOfEvents,
            'average_booking_duration' => $averageBookingDuration,
            // Include other metrics as needed
        ];
    }

    private function calculatePeakUsageTimes($bookings)
    {
        // Logic to analyze bookings and determine peak usage times
        // This might involve looking at the most common time slots or days
        // ...

        return $peakUsageTimes; // Example: ['Monday Morning', 'Friday Evening']
    }

    private function calculateTypesOfEvents($events)
    {
        // Analyze the types of events held in the facility
        // This might involve categorizing events and counting occurrences
        // ...

        return $eventTypes; // Example: ['Conferences' => 10, 'Weddings' => 5]
    }

    private function calculateAverageBookingDuration($bookings)
    {
        // Calculate the average duration of bookings
        // This might involve comparing the start and end times of each booking
        // ...

        return $averageDuration; // Example: '2.5 hours'
    }

    // Submitting a maintenance request for the facility
    public function submitMaintenanceRequest(Request $request, $facilityId)
    {
        $facility = Facility::findOrFail($facilityId);

        // Validate the request data
        $validatedData = $request->validate([
            'description' => 'required|string',
            'urgency' => 'required|in:low,medium,high',
            // Add other fields as necessary
        ]);

        // Create a new maintenance request
        $maintenanceRequest = new MaintenanceRequest($validatedData);
        $maintenanceRequest->facility_id = $facilityId;
        $maintenanceRequest->submitted_by = auth()->id(); // Assuming the user is authenticated
        $maintenanceRequest->status = 'pending'; // Initial status
        $maintenanceRequest->save();

        // Notify maintenance staff or department about the new request
        $this->notifyMaintenanceStaff($maintenanceRequest);

        return response()->json(['message' => 'Maintenance request submitted successfully'], Response::HTTP_OK);
    }

    /**
     * Notifies the maintenance staff or department about a new maintenance request.
     */
    private function notifyMaintenanceStaff(MaintenanceRequest $maintenanceRequest)
    {
        // Notification logic
        // This might involve sending emails, SMS, or internal notifications
        // Example: Notify via a dedicated maintenance channel or system
        // ...
    }

    // Submitting feedback for the facility
    public function submitFeedback(Request $request, $facilityId)
    {
        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Check if the user has the permission to submit feedback
        if (!Auth::user()->can('submit feedback')) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $facility = Facility::findOrFail($facilityId);

        // Validate the request data
        $validatedData = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comments' => 'nullable|string',
        ]);

        try {
            // Create a new feedback record
            $feedback = Feedback::create([
                'facility_id' => $facilityId,
                'user_id' => Auth::id(),
                'rating' => $validatedData['rating'],
                'comments' => $validatedData['comments'],
            ]);

            // Handle any additional actions based on the feedback
            $this->handleFeedbackActions($feedback);

            return response()->json(['message' => 'Feedback submitted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any exceptions during feedback submission
            return response()->json(['message' => 'Failed to submit feedback'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handleFeedbackActions(Feedback $feedback)
    {
        // Logic to handle actions based on feedback, such as triggering reviews or notifications
        // ...
    }

    // Additional methods as needed...
}
