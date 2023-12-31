<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(['name' => 'required|unique:roles']);
        $role = Role::create(['name' => $validatedData['name']]);
        return response()->json($role, 201);
    }


    public function show($id)
    {
        $role = Role::findById($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return response()->json($role);
    }


    public function update(Request $request, $id)
    {
        $role = Role::findById($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        $validatedData = $request->validate(['name' => 'required|unique:roles,name,' . $id]);
        $role->name = $validatedData['name'];
        $role->save();
        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findById($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        $role->delete();
        return response()->json(['message' => 'Role deleted']);
    }

    public function assignPermissionsToRole(Request $request, $roleId)
    {
        $role = Role::findById($roleId);

        $validatedData = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $permissions = $validatedData['permissions'];
        $role->syncPermissions($permissions);

        return response()->json(['message' => 'Permissions assigned to role successfully!']);
    }






}
