<?php

namespace App\Http\Controllers;

use App\Models\Folio;
use Illuminate\Http\Request;

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
        // Logic to add additional charges to the folio
    }

    // Example: Processing a payment against a folio
    public function processPayment($id, Request $request)
    {
        // Logic for recording a payment against the folio
    }
}

