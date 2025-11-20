<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['branches', 'defaultBranch'])
            ->paginate($request->integer('per_page', 25));

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load([
            'branches',
            'defaultBranch',
        ]));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'branches' => ['nullable', 'array'],
            'branches.*.branch_id' => ['required_with:branches', 'exists:branches,id'],
            'branches.*.role' => ['nullable', 'string', 'max:100'],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $user->fill(collect($data)->only(['name', 'phone', 'email'])->toArray());

        $passwordChanged = false;
        if (!empty($data['password'])) {
            $user->password = $data['password'];
            $passwordChanged = true;
        }

        if (array_key_exists('is_super_admin', $data)) {
            $user->is_super_admin = (bool) $data['is_super_admin'];
        }

        if (array_key_exists('default_branch_id', $data)) {
            $user->default_branch_id = $data['default_branch_id'];
        }

        $user->save();

        if ($passwordChanged) {
            $this->auth->markPasswordChanged($user);
        }

        if (array_key_exists('branches', $data)) {
            $branchPayload = collect($data['branches'] ?? [])->mapWithKeys(function ($branch) {
                $branchId = $branch['branch_id'];
                return [$branchId => ['role' => $branch['role'] ?? null]];
            });

            $user->branches()->sync($branchPayload->all());
        }

        return response()->json($user->load(['branches', 'defaultBranch']));
    }

    public function destroy(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }

    public function forcePasswordReset(User $user): JsonResponse
    {
        $this->auth->requirePasswordChange($user);
        $this->auth->requestPasswordReset($user);

        return response()->json(['message' => 'Password reset requested for user']);
    }
}
