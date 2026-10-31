<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UniversalAuthController extends Controller
{
    /**
     * Login Universal Terpadu Bank Mini Sekolah
     * Menerima username/identitas dan password.
     * Backend otomatis menentukan role pengguna (Nasabah, Teller, Supervisor, Admin)
     * berdasarkan kredensial yang diverifikasi langsung terhadap database.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $identity = trim($request->username);
        $password = (string) $request->password;

        // 1. Periksa apakah akun terdaftar di tabel Petugas (User: admin, supervisor, teller)
        $user = User::where('username', $identity)
            ->orWhere('email', $identity)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            $token = $user->createToken('petugas-token', ["role:{$user->role}"])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Petugas Berhasil',
                'token' => $token,
                'token_type' => 'Bearer',
                'role' => strtolower($user->role),
                'user_type' => 'petugas',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama_petugas' => $user->nama_petugas,
                    'role' => strtolower($user->role),
                    'kelas' => $user->kelas,
                    'no_hp' => $user->no_hp,
                    'email' => $user->email,
                    'foto_url' => $user->foto_url ?? null,
                ],
            ], 200);
        }

        // 2. Periksa apakah akun terdaftar di tabel Siswa (Nasabah)
        $nasabah = Nasabah::where('username', $identity)
            ->orWhere('nis', $identity)
            ->orWhere('no_rekening', $identity)
            ->first();

        if ($nasabah && Hash::check($password, $nasabah->password)) {
            // Validasi status rekening
            if (strtolower((string) $nasabah->status) !== 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun rekening Anda berstatus nonaktif atau terblokir. Silakan hubungi loket Bank Mini.',
                ], 403);
            }

            $token = $nasabah->createToken('nasabah-token', ['role:nasabah'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Nasabah Berhasil',
                'token' => $token,
                'token_type' => 'Bearer',
                'role' => 'nasabah',
                'user_type' => 'nasabah',
                'data' => [
                    'id_nasabah' => $nasabah->id,
                    'id' => $nasabah->id,
                    'username' => $nasabah->username,
                    'nama_siswa' => $nasabah->nama_siswa,
                    'nama_lengkap' => $nasabah->nama_siswa,
                    'no_rekening' => $nasabah->no_rekening,
                    'nis' => $nasabah->nis,
                    'kelas' => $nasabah->kelas,
                    'jurusan' => $nasabah->jurusan,
                    'saldo' => (int) $nasabah->saldo,
                    'total_saldo' => (int) $nasabah->saldo,
                    'status' => $nasabah->status,
                    'kategori' => $nasabah->kategori ?? 'SISWA',
                ],
            ], 200);
        }

        // 3. Kredensial tidak valid
        return response()->json([
            'success' => false,
            'message' => 'Kredensial login tidak valid! Silakan periksa kembali username dan kata sandi Anda.',
        ], 401);
    }

    /**
     * Profil Sesi Aktif Berdasarkan Bearer Token Sanctum
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

        if ($user instanceof User) {
            return response()->json([
                'success' => true,
                'role' => strtolower($user->role),
                'user_type' => 'petugas',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama_petugas' => $user->nama_petugas,
                    'role' => strtolower($user->role),
                    'kelas' => $user->kelas,
                    'no_hp' => $user->no_hp,
                    'email' => $user->email,
                ],
            ], 200);
        }

        if ($user instanceof Nasabah) {
            return response()->json([
                'success' => true,
                'role' => 'nasabah',
                'user_type' => 'nasabah',
                'data' => [
                    'id_nasabah' => $user->id,
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama_siswa' => $user->nama_siswa,
                    'nama_lengkap' => $user->nama_siswa,
                    'no_rekening' => $user->no_rekening,
                    'nis' => $user->nis,
                    'kelas' => $user->kelas,
                    'jurusan' => $user->jurusan,
                    'saldo' => (int) $user->saldo,
                    'total_saldo' => (int) $user->saldo,
                    'status' => $user->status,
                    'kategori' => $user->kategori ?? 'SISWA',
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tipe akun tidak dikenali.',
        ], 400);
    }

    /**
     * Logout Terpadu: Cabut Token Sesi Saat Ini
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil. Sesi dan token telah dicabut.',
        ], 200);
    }
}
