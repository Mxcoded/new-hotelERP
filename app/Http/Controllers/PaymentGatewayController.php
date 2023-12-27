<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    // List all payment gateways
    public function index()
    {
        $gateways = PaymentGateway::with(['transactions', 'invoices', 'properties'])->get();
        return response()->json($gateways);
    }

    // Show a single payment gateway with details
    public function show($id)
    {
        $gateway = PaymentGateway::with(['transactions', 'invoices', 'properties'])->find($id);
        return response()->json($gateway);
    }

    // Add a new payment gateway
    public function store(Request $request)
    {
        $gateway = PaymentGateway::create($request->all());
        // Additional logic for setting up and integrating the payment gateway
        return response()->json($gateway, 201);
    }

    // Update a payment gateway's details
    public function update(Request $request, $id)
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->update($request->all());
        // Additional logic for managing payment gateway configurations
        return response()->json($gateway, 200);
    }

    // Delete a payment gateway
    public function destroy($id)
    {
        PaymentGateway::find($id)->delete();
        // Additional cleanup logic if required, like adjusting linked transactions
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Processing a payment through a specific gateway
    public function processPayment($gatewayId, Request $request)
    {
        // Logic for processing a payment using the specified payment gateway
    }

    // Example: Updating gateway settings or credentials
    public function updateSettings($gatewayId, Request $request)
    {
        // Logic to update settings or integration details for a payment gateway
    }
}
