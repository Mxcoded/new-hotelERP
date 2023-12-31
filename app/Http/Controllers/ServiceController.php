<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    // List all services
    public function index()
    {
        $services = Service::all();
        return response()->json($services, 200);
    }

    // Show a specific service
    public function show($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return response()->json($service, 200);
    }

    // Create a new service
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $service = Service::create($request->all());
        return response()->json($service, 201);
    }

    // Update an existing service
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $service->update($request->all());
        return response()->json($service, 200);
    }

    // Delete a service
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $service->delete();
        return response()->json(['message' => 'Service deleted'], 200);
    }
}
