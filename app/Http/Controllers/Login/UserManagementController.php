<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::with(['defaultBranch', 'branches'])->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('login.users', [
            'users' => $users,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
            'branch_role' => ['nullable', Rule::in(['director', 'branch_manager', 'procurement', 'cashier', 'member'])],
            'is_super_admin' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'default_branch_id' => $data['default_branch_id'] ?? null,
            'is_super_admin' => (bool) ($data['is_super_admin'] ?? false),
            'must_change_password' => true,
        ]);

        $branchIds = collect($data['branches'] ?? [])
            ->when($data['default_branch_id'] ?? null, function ($collection, $defaultBranch) {
                return $collection->contains($defaultBranch) ? $collection : $collection->push($defaultBranch);
            })
            ->unique()
            ->values();

        if ($branchIds->isNotEmpty()) {
            $role = $data['branch_role'] ?? 'member';
            $user->branches()->sync(
                $branchIds->mapWithKeys(fn ($branchId) => [$branchId => ['role' => $role]])
                    ->all()
            );
        }

        return back()->with('status', 'Pengguna baru berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'default_branch_id' => ['nullable', 'exists:branches,id'],
            'is_super_admin' => ['nullable', 'boolean'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
            'branch_role' => ['nullable', Rule::in(['director', 'branch_manager', 'procurement', 'cashier', 'member'])],
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }

        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }

        if (!empty($data['password'])) {
            $user->password = $data['password'];
            $user->must_change_password = true;
        }

        if (array_key_exists('is_super_admin', $data)) {
            $user->is_super_admin = (bool) $data['is_super_admin'];
        }

        if (array_key_exists('default_branch_id', $data)) {
            $user->default_branch_id = $data['default_branch_id'];

            if ($data['default_branch_id'] && !$user->branches->contains('id', $data['default_branch_id'])) {
                $user->branches()->attach($data['default_branch_id'], ['role' => $data['branch_role'] ?? 'member']);
                $user->load('branches');
            }
        }

        if (array_key_exists('branches', $data)) {
            $branchIds = collect($data['branches'] ?? [])
                ->when($user->default_branch_id, function ($collection, $defaultBranch) {
                    return $collection->contains($defaultBranch) ? $collection : $collection->push($defaultBranch);
                })
                ->unique()
                ->values();

            $role = $data['branch_role'] ?? 'member';

            if ($branchIds->isEmpty()) {
                $user->branches()->detach();
            } else {
                $user->branches()->sync(
                    $branchIds->mapWithKeys(fn ($branchId) => [$branchId => ['role' => $role]])
                        ->all()
                );
            }
        }

        $user->save();

        return back()->with('status', 'Pengaturan pengguna telah diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'confirm' => ['nullable', 'string'],
        ]);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus akun yang sedang digunakan.']);
        }

        if ($user->is_super_admin && User::where('is_super_admin', true)->where('id', '!=', $user->id)->doesntExist()) {
            return back()->withErrors(['user' => 'Minimal harus ada satu super admin aktif.']);
        }

        $user->branches()->detach();
        $user->delete();

        return back()->with('status', 'Pengguna berhasil dihapus.');
    }
}
