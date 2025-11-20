<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::with('permissions')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:150'],
            'permission_ids' => ['nullable', 'array'],
        ]);

        $role = Role::create($data);

        if (!empty($data['permission_ids'])) {
            $role->permissions()->sync($data['permission_ids']);
        }

        return response()->json($role->load('permissions'), 201);
    }

    public function assign(Request $request, Role $role)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $role->users()->syncWithoutDetaching($data['user_ids']);

        return response()->json($role->load('users'));
    }

    public function attachPermission(Request $request, Role $role)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $role->permissions()->syncWithoutDetaching([$data['permission_id']]);

        return response()->json($role->load('permissions'));
    }

    public function syncUserRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $user->roles()->sync($data['role_ids']);

        return response()->json($user->load('roles'));
    }
}
