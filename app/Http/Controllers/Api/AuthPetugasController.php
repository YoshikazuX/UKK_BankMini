<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiAvalis;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthPetugasController extends Controller
{
    /**
     * Autentikasi Pegawai / Petugas (Teller, Supervisor, Administrator)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial login petugas tidak valid!',
            ], 401);
        }

        // Buat Sanctum token dengan ability role
        $token = $user->createToken('petugas-token', ["role:{$user->role}"])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Petugas Berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama_petugas' => $user->nama_petugas,
                'role' => $user->role,
                'kelas' => $user->kelas,
                'no_hp' => $user->no_hp,
                'email' => $user->email,
                'foto_url' => $user->foto_url,
            ],
        ], 200);
    }

    /**
     * Logout Petugas dan cabut token saat ini
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout Petugas Berhasil',
        ], 200);
    }

    /**
     * Ambil profil petugas saat ini & jadwal shift hari ini
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $jadwalHariIni = AbsensiAvalis::where('nama', $user->nama_petugas)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama_petugas' => $user->nama_petugas,
                'role' => $user->role,
                'kelas' => $user->kelas,
                'no_hp' => $user->no_hp,
                'email' => $user->email,
                'foto_url' => $user->foto_url,
                'shift_hari_ini' => $jadwalHariIni ? [
                    'shift' => $jadwalHariIni->shift_jaga,
                    'status' => $jadwalHariIni->status,
                    'keterangan' => $jadwalHariIni->keterangan,
                ] : null,
            ],
        ], 200);
    }
}

