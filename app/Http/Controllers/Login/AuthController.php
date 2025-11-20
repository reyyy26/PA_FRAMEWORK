<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'branches' => ['nullable', 'array'],
            'branches.*.branch_id' => ['required_with:branches', 'exists:branches,id'],
            'branches.*.role' => ['nullable', 'string', 'max:100'],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        if (User::exists()) {
            $actingUser = Auth::user();

            abort_unless($actingUser && $actingUser->is_super_admin, 403, 'Unauthorized');
        }

        $user = $this->auth->register($data);

        return response()->json([
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->auth->login($data['email'], $data['password']);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $this->auth->logout(Auth::user());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        abort_unless(Auth::check(), 401);

        return response()->json(Auth::user()->load(['branches', 'defaultBranch']));
    }

    public function requestPasswordReset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        /** @var User $user */
        $user = User::where('email', $data['email'])->firstOrFail();

        $this->auth->requestPasswordReset($user);

        return response()->json(['message' => 'Password reset initiated']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $this->auth->resetPassword($data['email'], $data['token'], $data['password']);

        return response()->json(['message' => 'Password updated']);
    }
}
