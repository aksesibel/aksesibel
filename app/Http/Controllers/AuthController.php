<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::attemptWhen([...$credentials, 'account_status' => 'active'], fn (User $user): bool => $user->account_status === 'active')) {
            throw ValidationException::withMessages(['email' => 'Email/password tidak sesuai atau akun tidak aktif.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account'));
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        $role = Role::query()->where('role_name', Role::PUBLIC)->get()->first(fn (Role $role) => $role->role_name === Role::PUBLIC);

        if (! $role) {
            throw ValidationException::withMessages(['email' => 'Peran Masyarakat belum tersedia. Pengelola perlu menjalankan MasterDataSeeder.']);
        }

        try {
            $user = User::create([...$request->safe()->only(['name', 'email', 'password']), 'role_id' => $role->role_id, 'account_status' => 'active']);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'Email sudah digunakan pengguna lain.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar.');
    }
}
