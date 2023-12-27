<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\GuestService;

class GuestController extends Controller
{
    // List all guests with additional details
    // public function index()
    // {
    //     $guests = Guest::with(['reservations', 'feedbacks', 'preferences'])->get();
    //     return response()->json($guests);
    // }

    public function index()
    {
        // Retrieve all guests with pagination
        $guests = Guest::all(); //paginate(10); // You can adjust the number of guests per page as needed

        // Return the paginated result
        return response()->json($guests);
    }

    // Show a single guest with detailed information
    public function show($id)
    {
        try {
            $guest = Guest::findOrFail($id);
            return response()->json($guest);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Guest not found'], 404);
        }
    }


    // Create a new guest profilepublic function store(Request $request)
    public function store(Request $request, GuestService $guestService)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'occupation' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nationality' => 'nullable|string',
            'vip' => 'boolean',
            'contact_type' => 'nullable|string',
            'email' => 'nullable|email|unique:guests,email',
            'country_code' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'address' => 'nullable|string',
            'identity_type' => 'nullable|string',
            'identity_id' => 'nullable|string',
            'front_id_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Example validation
            'back_id_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'guest_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as necessary
        ]);

        // Handle the image file
        if ($request->hasFile('front_id_image')) {
            $frontImagePath = $request->file('front_id_image')->store('id_images', 'public'); // Storing locally
            $validatedData['front_id_image'] = $frontImagePath; // Save the path in the database
        }

        if ($request->hasFile('back_id_image')) {
            $backImagePath = $request->file('back_id_image')->store('id_images', 'public'); // Storing locally
            $validatedData['back_id_image'] = $backImagePath; // Save the path in the database
        }

        if ($request->hasFile('guest_image')) {
            $guestImagePath = $request->file('guest_image')->store('guest_photos', 'public'); // Storing locally
            $validatedData['guest_image'] = $guestImagePath; // Save the path in the database
        }



        // Create the guest
        $guest = $guestService->createGuest($validatedData);

        // Additional logic for initial setup
        // For example, link guest to a reservation if reservation info is provided
        // Set initial preferences if available in the request

        // Return the newly created guest
        return response()->json($guest, 201);
    }


    // Update a guest's information
    public function update(Request $request, $id)
    {
        // Validate the request data
        // Include validation rules similar to the `store` method
        // Make sure to handle the 'unique' rule for the 'email' field
        $validatedData = $request->validate([
            'title' => 'sometimes|nullable|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|nullable|string',
            'occupation' => 'sometimes|nullable|string',
            'date_of_birth' => 'sometimes|nullable|date',
            'nationality' => 'sometimes|nullable|string',
            'vip' => 'sometimes|boolean',
            'contact_type' => 'sometimes|nullable|string',
            'email' => 'sometimes|nullable|email|unique:guests,email',
            'country_code' => 'sometimes|nullable|string',
            'mobile_number' => 'sometimes|nullable|string',
            'country' => 'sometimes|nullable|string',
            'state' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string',
            'zip_code' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            'identity_type' => 'sometimes|nullable|string',
            'identity_id' => 'sometimes|nullable|string',
            'front_id_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048', // Example validation
            'back_id_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
            'guest_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Add other validation rules as necessary

        ]);

        // Find the guest or fail with a 404 response
        $guest = Guest::findOrFail($id);

        // Handle image file updates
        if ($request->hasFile('front_id_image')) {
            $frontImagePath = $request->file('front_id_image')->store('id_images', 'public');
            $validatedData['front_id_image'] = $frontImagePath;
        }

        if ($request->hasFile('back_id_image')) {
            $backImagePath = $request->file('back_id_image')->store('id_images', 'public');
            $validatedData['back_id_image'] = $backImagePath;
        }

        if ($request->hasFile('guest_image')) {
            $guestImagePath = $request->file('guest_image')->store('guest_photos', 'public');
            $validatedData['guest_image'] = $guestImagePath;
        }

        // Update the guest with validated data
        $guest->update($validatedData);

        // Additional logic (if any)
        // For example, handle updates to guest preferences or link updated information to other related models

        // Return the updated guest data
        return response()->json($guest, 200);
    }


    // Delete a guest profile
    public function destroy($id)
    {
        Guest::find($id)->delete();
        // Additional cleanup logic if required (e.g., handling linked reservations)
        return response()->json(null, 204);
    }

    // Additional methods for specific guest operations...

    // Example: Handling loyalty program enrollment or points
    public function manageLoyaltyProgram($id, Request $request)
    {
        // Logic to manage the guest's loyalty program details
    }

    // Example: Fetching guest-specific reports or analytics
    public function fetchReports($id)
    {
        // Logic to generate and fetch reports specific to the guest
    }
}
