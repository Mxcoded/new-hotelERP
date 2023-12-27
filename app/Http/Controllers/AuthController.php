<?php
// app/Http/Controllers/AuthController.php
//use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Validator;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;



class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('HotelReservationApp')->accessToken;

            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function register(Request $request)
    {
        // Validate the request and create a new user

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('HotelReservationApp')->accessToken;

        return response()->json(['token' => $token], 201);
    }
}
