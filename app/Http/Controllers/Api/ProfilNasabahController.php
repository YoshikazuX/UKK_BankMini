<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ProfilNasabahController extends Controller
{
    // 1. CEK SALDO & DATA PROFIL
    public function saldo(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        $minMengendap = SettingHelper::getSaldoMengendapMin();
        $saldoTersedia = max(0, $nasabah->saldo - $minMengendap);

        return response()->json([
            'success' => true,
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'nama_siswa' => $nasabah->nama_siswa,
                'saldo' => (float) $nasabah->saldo,
                'formatted_saldo' => 'Rp '.number_format($nasabah->saldo, 0, ',', '.'),
                'saldo_mengendap' => (float) $minMengendap,
                'saldo_tersedia' => (float) $saldoTersedia,
            ],
        ]);
    }

    // 2. CEK REKENING TUJUAN (INQUIRY SEBELUM TRANSFER)
    public function cekRekening(Request $request): JsonResponse
    {
        $noRekening = $request->query('no_rekening') ?? $request->no_rekening;

        if (! $noRekening) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter no_rekening diperlukan',
            ], 400);
        }

        $nasabah = Nasabah::where('no_rekening', $noRekening)->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor rekening tujuan tidak ditemukan!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekening ditemukan',
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'jurusan' => $nasabah->jurusan,
            ],
        ]);
    }

    // 3. GENERATE PAYLOAD QRIS / KARTU DIGITAL NASABAH
    public function qrisPayload(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening nasabah tidak ditemukan!',
            ], 404);
        }

        $payload = sprintf(
            'BANKMINI:REK:%s:NIS:%s:NAMA:%s',
            $nasabah->no_rekening,
            $nasabah->nis,
            $nasabah->nama_siswa
        );

        return response()->json([
            'success' => true,
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'nama_siswa' => $nasabah->nama_siswa,
                'nis' => $nasabah->nis,
                'kelas' => $nasabah->kelas,
                'qr_string' => $payload,
                'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='.urlencode($payload),
            ],
        ]);
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

        $noRekening = $request->query('no_rekening') ?? $request->no_rekening;
        if ($noRekening) {
            return Nasabah::where('no_rekening', $noRekening)->first();
        }

        return null;
    }
}
