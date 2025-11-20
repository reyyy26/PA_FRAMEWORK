<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return response()->json(Permission::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'max:150'],
        ]);

        $permission = Permission::create($data);

        return response()->json($permission, 201);
    }
}
