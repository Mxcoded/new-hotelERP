<?php

namespace App\Http\Controllers;

use App\Models\Folio;
use App\Models\folio_charges;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Exception;

class FolioController extends Controller
{
    // List all folios
    public function index()
    {
        $folios = Folio::with(['reservation', 'guest', 'checkIn', 'charges'])->get();
        return response()->json($folios);
    }

    // Show a single folio with detailed information
    public function show($id)
    {
        $folio = Folio::with(['reservation', 'guest', 'checkIn', 'charges'])->find($id);
        return response()->json($folio);
    }

    // Create a new folio for a reservation
    public function store(Request $request)
    {
        // Validate request, ensure related reservation exists
        $folio = Folio::create($request->all());
        // Additional logic for initializing folio items (e.g., room charges)
        return response()->json($folio, 201);
    }

    // Update a folio (e.g., adding charges, processing payments)
    public function update(Request $request, $id)
    {
        $folio = Folio::findOrFail($id);
        $folio->update($request->all());
        // Additional logic for handling updates to folio items
        return response()->json($folio, 200);
    }

    // Delete a folio (if necessary)
    public function destroy($id)
    {
        Folio::find($id)->delete();
        // Handle any additional cleanup, if required
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Adding charges to a folio
    public function addCharge($id, Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Find the folio by ID
            $folio = Folio::findOrFail($id);

            // Create a new folio charge
            $folioCharge = new Folio_charges([
                'folio_id' => $folio->id,
                'description' => $validatedData['description'],
                'amount' => $validatedData['amount'],
                'date_incurred' => now(), // Set the charge date to now
            ]);
            $folioCharge->save();

            // Update the folio's total charges and balance
            $folio->total_charges += $validatedData['amount'];
            $folio->balance = $folio->total_charges - $folio->total_payments; // Update the balance
            $folio->save();

            DB::commit();
            return response()->json(['success' => true, 'folioCharge' => $folioCharge], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // Example: Processing a payment against a folio
    public function processPayment($id, Request $request)
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
}
