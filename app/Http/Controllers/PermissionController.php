<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(['name' => 'required|unique:permissions']);
        $permission = Permission::create(['name' => $validatedData['name']]);
        return response()->json($permission, 201);
    }

    public function show($id)
    {
        $permission = Permission::findById($id);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findById($id);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        $validatedData = $request->validate(['name' => 'required|unique:permissions,name,' . $id]);
        $permission->name = $validatedData['name'];
        $permission->save();
        return response()->json($permission);
    }

    public function destroy($id)
    {
        $permission = Permission::findById($id);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        $permission->delete();
        return response()->json(['message' => 'Permission deleted']);
    }



}
