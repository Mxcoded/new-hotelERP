<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Reservation;
use App\Models\Folio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



class TaxController extends Controller
{
    public function index()
    {
        // Fetch all tax records, including related properties and folios if applicable
        // Including 'reservations' is optional based on your system's requirements
        $taxes = Tax::with(['properties', 'folios'])
            ->get();

        // Optionally, transform the output for better readability or structure
        $transformedTaxes = $taxes->map(function ($tax) {
            return [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => $tax->rate,
                'type' => $tax->type,
                'is_global' => $tax->is_global,
                'is_active' => $tax->is_active,
                // Add other fields as needed
            ];
        });

        return response()->json($transformedTaxes);
    }

    public function show($id)
    {
        // Fetch a single tax record by id with related properties and folios
        $tax = Tax::with(['properties', 'folios'])->find($id);

        if (!$tax) {
            return response()->json(['message' => 'Tax not found'], 404);
        }

        // Transform the tax data for response
        $transformedTax = [
            'id' => $tax->id,
            'name' => $tax->name,
            'rate' => $tax->rate,
            'type' => $tax->type,
            'is_global' => $tax->is_global,
            'is_active' => $tax->is_active,
            'applicable_services' => $tax->applicable_services,
            // Add other fields as needed
        ];

        return response()->json($transformedTax);
    }


    // Create a new tax record
    public function store(Request $request)
    {
        // Validate incoming request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'type' => 'required|in:percentage,fixed',
            'applicable_services' => 'nullable|array',
            'is_global' => 'required|boolean',
            'is_active' => 'required|boolean'
        ]);

        // Create new Tax record
        $tax = Tax::create($validatedData);

        // Return the newly created tax record
        return response()->json($tax, 201);
    }


    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'rate' => 'sometimes|numeric',
            'type' => 'sometimes|in:percentage,fixed',
            'applicable_services' => 'nullable|array',
            'is_global' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean'
        ]);

        // Find the tax record or return 404 if not found
        $tax = Tax::findOrFail($id);

        // Update the tax record with validated data
        $tax->update($validatedData);

        // Return the updated tax record
        return response()->json($tax, 200);
    }

    public function destroy($id)
    {
        // Find the tax record or return 404 if not found
        $tax = Tax::findOrFail($id);

        // Perform any pre-deletion checks or cleanup if necessary
        // For example, ensure the tax is not applied to any active reservations or folios
        if ($tax->folios()->exists() || $tax->reservations()->exists()) {
            return response()->json(['message' => 'Cannot delete tax as it is applied to active folios or reservations'], 400);
        }

        // Delete the tax record
        $tax->delete();

        // Return a successful response
        return response()->json(['message' => 'Tax deleted successfully'], 200);
    }


    // Additional methods for specific operations...

    public function adjustTaxRate($taxId, Request $request)
    {
        // Validate incoming request
        $validatedData = $request->validate([
            'new_rate' => 'required|numeric',
            'effective_date' => 'required|date' // The date from which the new rate should be effective
        ]);

        // Find the tax record or return 404 if not found
        $tax = Tax::findOrFail($taxId);

        // Check if the new rate is different from the current rate
        if ($tax->rate == $validatedData['new_rate']) {
            return response()->json(['message' => 'The new rate is the same as the current rate'], 400);
        }

        // Update the tax rate
        $tax->update([
            'rate' => $validatedData['new_rate']
        ]);

        // Optionally: Log this change for auditing purposes
        Log::info("Tax rate for {$tax->name} changed to {$validatedData['new_rate']} effective from {$validatedData['effective_date']}");

        return response()->json(['message' => 'Tax rate adjusted successfully', 'tax' => $tax], 200);
    }


    public function applyToReservation($taxId, $reservationId)
    {
        // Find the tax and reservation records or return 404 if not found
        $tax = Tax::findOrFail($taxId);
        $reservation = Reservation::findOrFail($reservationId);

        // Check if the tax is applicable to the reservation (based on service type, global flag, etc.)
        if (!$tax->isApplicableToService($reservation->service_id)) { // Assuming service_id is a field in Reservation
            return response()->json(['message' => 'Tax is not applicable to this reservation'], 400);
        }

        // Apply the tax to the reservation
        // This could involve adding a new line item to the reservation's folio for the tax amount
        $folio = $reservation->folio; // Assuming a folio is linked to the reservation
        $taxAmount = $tax->calculateTaxAmount($reservation->price); // Assuming a method to calculate the tax amount

        $folio->charges()->create([
            'description' => "Tax: {$tax->name}",
            'amount' => $taxAmount,
            'date_incurred' => now(),
            'charge_type' => 'Tax'
        ]);

        return response()->json(['message' => 'Tax applied to reservation successfully'], 200);
    }


    public function applyTax($folioId, $taxId)
    {
        // Find the folio and tax records or return 404 if not found
        $folio = Folio::findOrFail($folioId);
        $tax = Tax::findOrFail($taxId);

        // Check if the tax is applicable to the folio's services
        // Assuming the folio is linked to specific services or reservations
        if (!$tax->isApplicableToService($folio->service_id)) {
            return response()->json(['message' => 'Tax is not applicable to this folio'], 400);
        }

        // Calculate the tax amount based on the folio's total charges
        $taxAmount = $tax->calculateTaxAmount($folio->total_charges);

        // Apply the tax to the folio
        $folio->charges()->create([
            'description' => "Tax: {$tax->name}",
            'amount' => $taxAmount,
            'date_incurred' => now(),
            'charge_type' => 'Tax'
        ]);

        // Optionally, update the folio's total
        $folio->update(['total_charges' => $folio->total_charges + $taxAmount]);

        return response()->json(['message' => 'Tax applied to folio successfully'], 200);
    }
    public function listApplicableTaxesForService($serviceId)
    {
        // Fetch taxes that are either global or specifically applicable to the given service
        $taxes = Tax::where('is_global', true)
            ->orWhereJsonContains('applicable_services', $serviceId)
            ->get();

        // Transform the data for better readability
        $transformedTaxes = $taxes->map(function ($tax) {
            return [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => $tax->rate,
                'type' => $tax->type,
                'is_active' => $tax->is_active
                // Include other necessary fields
            ];
        });

        return response()->json($transformedTaxes);
    }

    public function toggleTaxActivation($taxId)
    {
        // Find the tax record or return 404 if not found
        $tax = Tax::findOrFail($taxId);

        // Toggle the is_active status
        $tax->is_active = !$tax->is_active;
        $tax->save();

        $status = $tax->is_active ? 'activated' : 'deactivated';
        return response()->json(['message' => "Tax has been successfully $status", 'tax' => $tax], 200);
    }

    /**
     * Calculate the tax amount based on the base amount and tax rate.
     *
     * @param float $baseAmount The amount on which tax is to be calculated.
     * @param Tax $tax The tax object containing the rate and type.
     * @return float The calculated tax amount.
     */
    private function calculateTaxAmount($baseAmount, Tax $tax)
    {
        if ($tax->type === 'percentage') {
            // Calculate percentage-based tax
            return ($baseAmount * $tax->rate) / 100;
        } else {
            // For fixed type, return the fixed amount
            return $tax->rate;
        }
    }


};
