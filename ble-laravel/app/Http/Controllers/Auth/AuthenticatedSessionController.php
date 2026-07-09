<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login (admin / dosen).
     * Redirect ke dashboard masing-masing jika sudah login.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('dosen')->check()) {
            return redirect()->route('dosen.rekap');
        }

        return view('auth.login');
    }

    /**
     * Proses login berdasarkan role yang dipilih.
     * Role 'admin'  → guard 'web'   → redirect ke admin.dashboard
     * Role 'dosen'  → guard 'dosen' → redirect ke dosen.rekap
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role'     => ['required', 'in:admin,dosen'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'role.required'     => 'Pilih role terlebih dahulu.',
            'role.in'           => 'Role tidak valid.',
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // ── LOGIN ADMIN ─────────────────────────────────────────────
        if ($request->role === 'admin') {

            $admin = Admin::where('username', $request->username)->first();

            if (! $admin || ! Hash::check($request->password, $admin->password)) {
                throw ValidationException::withMessages([
                    'username' => 'Username atau password admin tidak valid.',
                ]);
            }

            Auth::guard('web')->login($admin, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang, ' . $admin->nama . '!');
        }

        // ── LOGIN DOSEN ─────────────────────────────────────────────
        $dosen = Dosen::where('username', $request->username)->first();

        if (! $dosen || ! Hash::check($request->password, $dosen->password)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password dosen tidak valid.',
            ]);
        }

        Auth::guard('dosen')->login($dosen, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dosen.rekap'))
            ->with('success', 'Selamat datang, ' . $dosen->nama . '!');
    }

    /**
     * Logout — handle semua guard yang aktif.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        Auth::guard('dosen')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}