<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $dashboardRoute = $this->dashboardRoute($user);

            abort_if($dashboardRoute === null, 403, 'Akses ditolak.');

            return redirect()->route($dashboardRoute);
        }

        return view('welcome');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak benar.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $dashboardRoute = $this->dashboardRoute($request->user());

        if ($dashboardRoute === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Email atau password tidak benar.'])
                ->onlyInput('email');
        }

        return redirect()->intended(route($dashboardRoute));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(?User $user): ?string
    {
        return match ($user?->role) {
            'super_admin' => 'superadmin.dashboard',
            'admin_instansi', 'developer' => 'admin.dashboard',
            default => null,
        };
    }
}
