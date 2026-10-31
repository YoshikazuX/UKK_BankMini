<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class NotificationApiController extends Controller
{
    /**
     * Resolusi entitas pengguna (Nasabah atau Petugas User).
     */
    private function resolveUser(Request $request): User|Nasabah|null
    {
        $user = $request->user();
        if ($user instanceof User || $user instanceof Nasabah) {
            return $user;
        }

        if ($token = $request->bearerToken()) {
            $pat = PersonalAccessToken::findToken($token);
            if ($pat && ($pat->tokenable instanceof User || $pat->tokenable instanceof Nasabah)) {
                return $pat->tokenable;
            }
        }

        return null;
    }

    /**
     * Mengambil daftar notifikasi pengguna (Nasabah atau Petugas).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $userType = get_class($user);
        $notifications = DB::table('notifications')
            ->where('notifiable_type', $userType)
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $list = [];

        foreach ($notifications as $n) {
            $data = json_decode($n->data, true) ?? [];
            $list[] = [
                'id' => (string) $n->id,
                'title' => $data['title'] ?? ($data['pesan'] ?? 'Notifikasi Bank Mini'),
                'message' => $data['message'] ?? ($data['keterangan'] ?? ''),
                'type' => $data['task_type'] ?? ($data['jenis'] ?? 'SISTEM'),
                'timestamp' => $n->created_at,
                'is_read' => $n->read_at !== null,
            ];
        }

        // Jika notifikasi di tabel database belum ada dan pengguna adalah Nasabah,
        // bangun notifikasi transaksional aktual secara otomatis dari riwayat transaksi riil.
        if (empty($list) && $user instanceof Nasabah) {
            $transaksis = Transaksi::where('nasabah_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get();

            if (strtolower((string) $user->status) === 'nonaktif') {
                $list[] = [
                    'id' => 'alert_freeze_'.$user->id,
                    'title' => 'Rekening Dibekukan Sementara',
                    'message' => 'Rekening tabungan Anda saat ini berstatus nonaktif/dibekukan untuk perlindungan darurat.',
                    'type' => 'KEAMANAN',
                    'timestamp' => $user->updated_at?->format('Y-m-d H:i:s') ?? now()->toIso8601String(),
                    'is_read' => false,
                ];
            }

            foreach ($transaksis as $t) {
                $isSetor = in_array($t->jenis_transaksi, ['setor', 'transfer_masuk']);
                $title = $isSetor ? 'Setoran Tunai Berhasil' : 'Penarikan Tunai Berhasil';
                $nominalStr = 'Rp '.number_format($t->nominal, 0, ',', '.');
                $message = "Transaksi {$t->jenis_transaksi} sebesar {$nominalStr} dengan status {$t->status}.";
                if (! empty($t->keterangan)) {
                    $message .= " Catatan: {$t->keterangan}";
                }

                $list[] = [
                    'id' => 'trx_'.$t->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $isSetor ? 'SETORAN' : 'PENARIKAN',
                    'timestamp' => $t->created_at?->format('Y-m-d H:i:s') ?? now()->toIso8601String(),
                    'is_read' => true,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil dimuat',
            'count' => count($list),
            'data' => $list,
        ], 200);
    }

    /**
     * Jumlah notifikasi belum dibaca.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'unread_count' => 0], 401);
        }

        $count = DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ], 200);
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai telah dibaca',
        ], 200);
    }

    /**
     * Tandai seluruh notifikasi pengguna sebagai telah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Seluruh notifikasi berhasil ditandai telah dibaca',
        ], 200);
    }
}
