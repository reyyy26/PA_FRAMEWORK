<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AuthService
{
    public function __construct(private readonly NotificationService $notifier)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @throws Throwable
     */
    public function register(array $payload): User
    {
        return DB::transaction(function () use ($payload) {
            $userData = Arr::only($payload, ['name', 'email', 'phone']);
            $userData['password'] = $payload['password'] ?? Str::password();
            $userData['password_changed_at'] = now();
            $userData['is_super_admin'] = (bool) ($payload['is_super_admin'] ?? false);
            $userData['default_branch_id'] = $payload['default_branch_id'] ?? null;

            /** @var User $user */
            $user = User::create($userData);

            if (!empty($payload['branches'])) {
                $branchPayload = collect($payload['branches'])
                    ->mapWithKeys(function ($branch) {
                        $branchId = is_array($branch) ? ($branch['branch_id'] ?? $branch['id'] ?? null) : $branch;
                        if (!$branchId) {
                            return [];
                        }

                        $role = is_array($branch) ? ($branch['role'] ?? null) : null;

                        return [$branchId => ['role' => $role]];
                    });

                if ($branchPayload->isNotEmpty()) {
                    $user->branches()->sync($branchPayload->all(), false);
                }
            }

            $this->notifier->notify('auth.registered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $user->fresh(['branches', 'defaultBranch']);
        });
    }

    public function login(string $email, string $password): User
    {
        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        $isValid = $user && (
            Hash::check($password, $user->password)
            || hash_equals($user->password, $password)
        );

        abort_unless($isValid, 422, 'Invalid credentials');

        $this->notifier->notify('auth.login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'at' => now()->toIso8601String(),
        ]);

        return $user->load(['branches', 'defaultBranch']);
    }

    public function logout(User $user): void
    {
        $this->notifier->notify('auth.logout', [
            'user_id' => $user->id,
            'email' => $user->email,
            'at' => now()->toIso8601String(),
        ]);
    }

    public function requestPasswordReset(User $user): void
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $this->notifier->notify('auth.password_reset_requested', [
            'email' => $user->email,
            'token' => $token,
        ]);
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        abort_unless($record && Hash::check($token, $record->token), 422, 'Invalid reset token');

        /** @var User $user */
        $user = User::where('email', $email)->firstOrFail();
        $user->forceFill([
            'password' => Hash::make($password),
            'password_changed_at' => now(),
            'must_change_password' => false,
        ])->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $this->notifier->notify('auth.password_reset_completed', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function requirePasswordChange(User $user): void
    {
        $user->update(['must_change_password' => true]);
    }

    public function markPasswordChanged(User $user): void
    {
        $user->update([
            'password_changed_at' => now(),
            'must_change_password' => false,
        ]);
    }
}
