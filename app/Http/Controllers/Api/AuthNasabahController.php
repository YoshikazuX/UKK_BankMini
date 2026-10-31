<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthNasabahController extends Controller
{
    // 1. LOGIN NASABAH VIA MOBILE
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $nasabah = Nasabah::where('username', $request->username)->first();

        if (! $nasabah || ! Hash::check($request->password, $nasabah->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah!',
            ], 401);
        }

        // Generate Sanctum Bearer Token
        $token = $nasabah->createToken('nasabah-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => [
                'id_nasabah' => $nasabah->id,
                'username' => $nasabah->username,
                'nama_siswa' => $nasabah->nama_siswa,
                'no_rekening' => $nasabah->no_rekening,
            ],
        ], 200);
    }

    // 2. LOGOUT NASABAH VIA MOBILE
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil',
        ], 200);
    }

    // 3. VERIFIKASI PIN 6-DIGIT OTORISASI
    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required',
            'pin' => 'required|digits:6',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)->first();

        if (! $nasabah || ! Hash::check($request->pin, $nasabah->pin_keamanan)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN 6-digit keamanan salah!',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Otorisasi PIN Berhasil!',
        ]);
    }

    // 4. GANTI PASSWORD LOGIN
    public function gantiPassword(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required',
            'password_lama' => 'required',
            'password_baru' => 'required|min:6',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak ditemukan!',
            ], 404);
        }

        if (! Hash::check($request->password_lama, $nasabah->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai!',
            ], 400);
        }

        $nasabah->update([
            'password' => $request->password_baru,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password login berhasil diperbarui!',
        ]);
    }

    // 5. GANTI PIN TRANSAKSI (6-DIGIT)
    public function gantiPin(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required',
            'pin_lama' => 'required|digits:6',
            'pin_baru' => 'required|digits:6',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak ditemukan!',
            ], 404);
        }

        if (! Hash::check($request->pin_lama, $nasabah->pin_keamanan)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN lama salah!',
            ], 400);
        }

        $nasabah->update([
            'pin_keamanan' => Hash::make($request->pin_baru),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN transaksi 6-digit berhasil diperbarui!',
        ]);
    }

    // 6. AMBIL DATA NASABAH OTENTIKASI SAAT INI
    public function me(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_nasabah' => $nasabah->id,
                'nis' => $nasabah->nis,
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'jurusan' => $nasabah->jurusan,
                'no_rekening' => $nasabah->no_rekening,
                'username' => $nasabah->username,
                'email' => $nasabah->email,
                'no_hp' => $nasabah->no_hp,
                'saldo' => (float) $nasabah->saldo,
                'formatted_saldo' => 'Rp '.number_format($nasabah->saldo, 0, ',', '.'),
                'status' => $nasabah->status,
                'has_pin' => ! empty($nasabah->pin_keamanan),
            ],
        ], 200);
    }

    // 7. PERBARUI KONTAK NASABAH
    public function updateProfil(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validated = $request->validate([
            'email' => 'nullable|email|max:100|unique:nasabah,email,'.$nasabah->id,
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'],
        ], [
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar pada rekening nasabah lain.',
            'no_hp.regex' => 'Format nomor telepon tidak valid. Gunakan angka standar Indonesia.',
        ]);

        $oldValues = [
            'email' => $nasabah->email,
            'no_hp' => $nasabah->no_hp,
        ];

        $nasabah->update(array_filter($validated, fn ($val) => $val !== null));

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_PROFIL_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: $oldValues,
            newValues: [
                'email' => $nasabah->email,
                'no_hp' => $nasabah->no_hp,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profil nasabah berhasil diperbarui!',
            'data' => [
                'id_nasabah' => $nasabah->id,
                'nama_siswa' => $nasabah->nama_siswa,
                'email' => $nasabah->email,
                'no_hp' => $nasabah->no_hp,
            ],
        ], 200);
    }

    /**
     * Resolusi entitas Nasabah dari Sanctum Token atau No Rekening
     */
    private function resolveNasabah(Request $request): ?Nasabah
    {
        $user = $request->user();
        if ($user instanceof Nasabah) {
            return $user;
        }

        if ($token = $request->bearerToken()) {
            $pat = PersonalAccessToken::findToken($token);
            if ($pat && $pat->tokenable instanceof Nasabah) {
                return $pat->tokenable;
            }
        }

        if ($request->filled('no_rekening')) {
            return Nasabah::where('no_rekening', $request->no_rekening)->first();
        }

        return null;
    }
}
