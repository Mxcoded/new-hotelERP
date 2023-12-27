<?php

namespace App\Http\Controllers;

use App\Models\CancellationPolicy;
use Illuminate\Http\Request;

class CancellationPolicyController extends Controller
{
    // Example method for creating a cancellation policy
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'cancellation_deadline' => 'required|integer',
            'cancellation_fee' => 'required|numeric',
            'is_refundable' => 'required|boolean'
        ]);

        $policy = CancellationPolicy::create($validatedData);

        return response()->json(['success' => true, 'data' => $policy], 201);
    }

    // Add methods for show, update, delete, and other necessary operations
}
