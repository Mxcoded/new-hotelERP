<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // List all users
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    // Show a single user with roles
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        return response()->json($user);
    }

    // Create a new user with a role
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            // 'property_id' => 'nullable|exists:properties,id',
            // 'employee_id' => 'nullable|string|unique:users,employee_id',
            // 'position' => 'nullable|string',
            // 'department' => 'nullable|string',
            // 'active' => 'boolean',
            // 'login_access' => 'boolean',
            // 'phone_number' => 'nullable|string',
            // 'address' => 'nullable|string',
            // 'hire_date' => 'nullable|date',
            // 'termination_date' => 'nullable|date',
            // 'created_by' => 'nullable|exists:users,id',
            // 'updated_by' => 'nullable|exists:users,id',
            // 'role' => 'required|exists:roles,name'
            // Add validation for any other new fields
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $userData = $validator->validated();
        $userData['password'] = Hash::make($userData['password']);
        $user = User::create($userData);

        // $role = Role::findByName($request->role);
        // $user->assignRole($role);

        return response()->json($user, 201);
    }

    // Update a user and their role
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable',
            'property_id' => 'nullable|exists:properties,id',
            // ... include other fields as in the store method
            'role' => 'sometimes|required|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::findOrFail($id);
        $userData = $validator->validated();

        if (!empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        $user->update($userData);

        if ($request->filled('role')) {
            $user->syncRoles($request->role);
        }

        return response()->json($user, 200);
    }

    // Delete a user
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    // Additional methods as needed...
}
