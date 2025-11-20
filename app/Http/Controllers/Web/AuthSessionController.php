<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AuthSessionController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function create(): RedirectResponse|View|Factory
    {
        if (Auth::check()) {
            return redirect()->intended($this->homePathFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $user = $this->auth->login(
                $data['email'],
                $data['password']
            );
        } catch (HttpExceptionInterface $exception) {
            $status = $exception->getStatusCode();

            $message = $status === 422
                ? 'Email atau password tidak valid.'
                : $exception->getMessage();

            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => $message]);
        }

        Auth::login($user);

        $request->session()->regenerate();
        $this->applyBranchContext($request, $user);

        return redirect()->intended($this->homePathFor($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $this->auth->logout(Auth::user());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function applyBranchContext(Request $request, $user): void
    {
        if ($user->is_super_admin) {
            $request->session()->forget('branch_id');

            return;
        }

        $branchId = $user->default_branch_id;

        if (!$branchId && $user->branches()->exists()) {
            $branchId = $user->branches()->first()->id;
        }

        if ($branchId) {
            $request->session()->put('branch_id', $branchId);
        }
    }

    private function homePathFor($user): string
    {
        if ($user?->is_super_admin) {
            return '/dashboard/main';
        }
        $user?->loadMissing('branches');

        $branchRole = null;

        if ($user?->default_branch_id) {
            $branchRole = $user->branches?->firstWhere('id', $user->default_branch_id)?->pivot?->role;
        }

        if (!$branchRole) {
            $branchRole = $user->branches?->first()?->pivot?->role;
        }

        if ($branchRole === 'cashier') {
            return '/pos/sales/quick';
        }

        return '/dashboard/branch';
    }
}
