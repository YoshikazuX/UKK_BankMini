<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AuditHelper;
use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Nasabah;
use App\Models\Transaksi;
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
                'status' => $nasabah->status,
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

    // 4. EMERGENCY ACCOUNT FREEZE (BLOKIR REKENING MANDIRI OLEH SISWA)
    public function emergencyFreeze(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (strtolower((string) $nasabah->status) === 'nonaktif') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening sudah dalam status nonaktif / dibekukan.',
                'data' => [
                    'no_rekening' => $nasabah->no_rekening,
                    'status' => 'nonaktif',
                ],
            ], 400);
        }

        $oldStatus = $nasabah->status;
        $nasabah->status = 'nonaktif';
        $nasabah->save();

        // Rekam jejak audit trail
        AuditHelper::log(
            jenisAktivitas: 'EMERGENCY_FREEZE_MANDIRI',
            modul: 'Nasabah',
            objekId: $nasabah->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'nonaktif'],
            user: null
        );

        return response()->json([
            'success' => true,
            'message' => 'Rekening Anda berhasil dibekukan secara mandiri demi keamanan. Seluruh penarikan di loket dilarang hingga diaktifkan kembali oleh Administrator.',
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'status' => 'nonaktif',
            ],
        ], 200);
    }

    // 5. EVALUASI LENCANA PRESTASI MENABUNG (BADGES GAMIFICATION)
    public function badges(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $setoranCount = Transaksi::where('nasabah_id', $nasabah->id)
            ->where('status', 'sukses')
            ->whereIn('jenis_transaksi', ['setor', 'transfer_masuk'])
            ->count();

        $firstDeposit = Transaksi::where('nasabah_id', $nasabah->id)
            ->where('status', 'sukses')
            ->whereIn('jenis_transaksi', ['setor', 'transfer_masuk'])
            ->oldest()
            ->first();

        $saldo = (float) $nasabah->saldo;
        $totalDeposit = (float) Transaksi::where('nasabah_id', $nasabah->id)
            ->where('status', 'sukses')
            ->whereIn('jenis_transaksi', ['setor', 'transfer_masuk'])
            ->sum('nominal');

        $badges = [
            [
                'id' => 'badge_1',
                'nama' => 'Penabung Perdana',
                'deskripsi' => 'Melakukan setoran pertama tabungan di loket',
                'icon' => 'savings',
                'tier' => 'bronze',
                'is_unlocked' => $setoranCount >= 1,
                'unlocked_at' => $firstDeposit?->created_at?->format('Y-m-d H:i:s'),
            ],
            [
                'id' => 'badge_2',
                'nama' => 'Disiplin Menabung',
                'deskripsi' => 'Mencapai total saldo tabungan aktif minimal Rp 100.000',
                'icon' => 'military_tech',
                'tier' => 'silver',
                'is_unlocked' => $saldo >= 100000,
                'unlocked_at' => $saldo >= 100000 ? $nasabah->updated_at?->format('Y-m-d H:i:s') : null,
            ],
            [
                'id' => 'badge_3',
                'nama' => 'Pahlawan Finansial',
                'deskripsi' => 'Mencapai saldo Rp 500.000 atau konsisten menabung minimal 5 kali transaksi',
                'icon' => 'stars',
                'tier' => 'gold',
                'is_unlocked' => $saldo >= 500000 || $setoranCount >= 5,
                'unlocked_at' => ($saldo >= 500000 || $setoranCount >= 5) ? $nasabah->updated_at?->format('Y-m-d H:i:s') : null,
            ],
            [
                'id' => 'badge_4',
                'nama' => 'Master Saver',
                'deskripsi' => 'Mencapai total saldo tabungan sebesar Rp 1.000.000',
                'icon' => 'workspace_premium',
                'tier' => 'gold',
                'is_unlocked' => $saldo >= 1000000,
                'unlocked_at' => $saldo >= 1000000 ? $nasabah->updated_at?->format('Y-m-d H:i:s') : null,
            ],
            [
                'id' => 'badge_5',
                'nama' => 'Disiplin Finansial',
                'deskripsi' => 'Mencapai total akumulasi simpanan sebesar Rp 2.000.000',
                'icon' => 'verified',
                'tier' => 'gold',
                'is_unlocked' => $totalDeposit >= 2000000,
                'unlocked_at' => $totalDeposit >= 2000000 ? $nasabah->updated_at?->format('Y-m-d H:i:s') : null,
            ],
            [
                'id' => 'badge_6',
                'nama' => 'Duta Literasi',
                'deskripsi' => 'Mencapai saldo tabungan Rp 5.000.000 dan aktif menabung di sekolah',
                'icon' => 'diamond',
                'tier' => 'gold',
                'is_unlocked' => $saldo >= 5000000,
                'unlocked_at' => $saldo >= 5000000 ? $nasabah->updated_at?->format('Y-m-d H:i:s') : null,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Lencana prestasi menabung berhasil dievaluasi',
            'data' => $badges,
        ], 200);
    }

    // 6. RIWAYAT SESI LOGIN AKTIF (SESSION LOGS)
    public function sessions(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $currentToken = $request->bearerToken();
        $currentPatId = null;
        if ($currentToken) {
            $pat = PersonalAccessToken::findToken($currentToken);
            $currentPatId = $pat?->id;
        }

        $tokens = PersonalAccessToken::where('tokenable_type', Nasabah::class)
            ->where('tokenable_id', $nasabah->id)
            ->orderBy('last_used_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $sessions = [];
        foreach ($tokens as $t) {
            $isCurrent = $currentPatId && $t->id == $currentPatId;
            $sessions[] = [
                'id' => (string) $t->id,
                'device_name' => $t->name ?? 'Aplikasi Mobile Bank Mini',
                'device_type' => str_contains(strtolower($t->name), 'android') ? 'android' : (str_contains(strtolower($t->name), 'ios') ? 'ios' : 'mobile'),
                'ip_address' => $isCurrent ? $request->ip() : 'Koneksi Terverifikasi',
                'location' => 'Lingkungan Sekolah SMK',
                'last_access' => ($t->last_used_at ?? $t->created_at)->format('Y-m-d H:i:s'),
                'is_current_session' => $isCurrent,
            ];
        }

        if (empty($sessions)) {
            $sessions[] = [
                'id' => 'sess_active',
                'device_name' => 'Aplikasi Mobile (Sesi Aktif)',
                'device_type' => 'mobile',
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'location' => 'Lingkungan Sekolah SMK',
                'last_access' => now()->format('Y-m-d H:i:s'),
                'is_current_session' => true,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat sesi login berhasil dimuat',
            'data' => $sessions,
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

        $noRekening = $request->query('no_rekening') ?? $request->no_rekening;
        if ($noRekening) {
            return Nasabah::where('no_rekening', $noRekening)->first();
        }

        return null;
    }
}
