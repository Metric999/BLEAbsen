<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role'     => 'required|in:mahasiswa,dosen',
        ]);

        if ($request->role === 'mahasiswa') {
            $user = Mahasiswa::where('username', $request->username)->first();
        } else {
            $user = Dosen::where('username', $request->username)->first();
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        $token = $user->createToken('ble-absen-' . $request->role)->plainTextToken;

        return response()->json([
            'token' => $token,
            'role'  => $request->role,
            'user'  => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_lama'         => 'required|string',
            'password_baru'         => 'required|string|min:6',
            'password_baru_confirm' => 'required|string|same:password_baru',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            throw ValidationException::withMessages([
                'password_lama' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}