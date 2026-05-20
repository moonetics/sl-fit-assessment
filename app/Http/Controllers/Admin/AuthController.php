<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::query()->where('email', $validated['email'])->first();

        if (! $admin || ! $admin->password_hash || ! Hash::check($validated['password'], $admin->password_hash)) {
            return back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => 'Email atau password admin tidak valid.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_id', $admin->id);
        $admin->update(['last_login_at' => now()]);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_id');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
