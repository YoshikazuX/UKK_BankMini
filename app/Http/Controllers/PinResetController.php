<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Nasabah;
use App\Models\PinResetRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PinResetController extends Controller
{
    /**
     * Nasabah mengajukan permohonan lupa PIN ke Admin (Web Portal)
     */
    public function requestReset(Request $request): RedirectResponse
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'alasan' => 'nullable|string|max:255',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)
            ->orWhere('nis', $request->no_rekening)
            ->first();

        if (! $nasabah) {
            return back()->with('error', 'Data rekening nasabah tidak ditemukan.');
        }

        // Cek jika sudah ada permohonan yang aktif
        $activeRequest = PinResetRequest::where('nasabah_id', $nasabah->id)
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();

        if ($activeRequest) {
            if ($activeRequest->status === 'approved') {
                return back()->with('success', 'Permohonan reset PIN Anda telah disetujui Admin! Silakan masukkan 6 digit PIN baru Anda secara mandiri di bawah.');
            }

            return back()->with('error', 'Permohonan reset PIN Anda sebelumnya masih menunggu verifikasi & persetujuan dari Admin.');
        }

        $resetReq = PinResetRequest::create([
            'nasabah_id' => $nasabah->id,
            'no_rekening' => $nasabah->no_rekening,
            'nis' => $nasabah->nis,
            'nama_siswa' => $nasabah->nama_siswa,
            'alasan' => $request->alasan ?: 'Lupa PIN Kartu Tabungan Bank Mini',
            'status' => 'pending',
            'reset_token' => Str::random(40),
        ]);

        AuditHelper::log(
            jenisAktivitas: 'AJUKAN_LUPA_PIN',
            modul: 'Nasabah',
            objekId: "#PIN-REQ-{$resetReq->id}",
            oldValues: null,
            newValues: [
                'no_rekening' => $nasabah->no_rekening,
                'status' => 'pending',
            ]
        );

        return back()->with('success', 'Permohonan reset PIN berhasil dikirimkan ke Admin. Mohon tunggu persetujuan Admin sebelum membuat PIN baru.');
    }

    /**
     * Nasabah membuat PIN baru secara mandiri setelah disetujui Admin (Web Portal)
     */
    public function setNewPin(Request $request): RedirectResponse
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'pin_baru' => 'required|digits:6',
            'pin_baru_confirmation' => 'required|same:pin_baru',
        ], [
            'pin_baru.digits' => 'PIN harus tepat 6 digit angka.',
            'pin_baru_confirmation.same' => 'Konfirmasi PIN tidak cocok dengan PIN baru.',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)
            ->orWhere('nis', $request->no_rekening)
            ->first();

        if (! $nasabah) {
            return back()->with('error', 'Data rekening nasabah tidak ditemukan.');
        }

        // Cek permohonan yang telah disetujui Admin
        $approvedRequest = PinResetRequest::where('nasabah_id', $nasabah->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (! $approvedRequest) {
            return back()->with('error', 'Gagal: Permohonan reset PIN Anda belum disetujui Admin atau belum diajukan.');
        }

        // Update PIN nasabah
        $nasabah->update([
            'pin_keamanan' => Hash::make($request->pin_baru),
        ]);

        // Selesaikan tiket permohonan
        $approvedRequest->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        AuditHelper::log(
            jenisAktivitas: 'SET_PIN_MANDIRI',
            modul: 'Nasabah',
            objekId: "#PIN-REQ-{$approvedRequest->id}",
            oldValues: ['status' => 'approved'],
            newValues: ['status' => 'completed']
        );

        return back()->with('success', 'PIN kartu tabungan baru berhasil dibuat secara mandiri! Silakan simpan dan ingat 6 digit PIN Anda.');
    }

    /**
     * Admin Menyetujui Permohonan Reset PIN (Admin TIDAK membuatkan PIN)
     */
    public function adminApprove(int|string $id): RedirectResponse
    {
        $pinReq = PinResetRequest::with('nasabah')->findOrFail($id);

        if ($pinReq->status !== 'pending') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $pinReq->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);

        AuditHelper::log(
            jenisAktivitas: 'APPROVE_RESET_PIN',
            modul: 'Nasabah',
            objekId: "#PIN-REQ-{$pinReq->id}",
            oldValues: ['status' => 'pending'],
            newValues: [
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]
        );

        return back()->with('success', "Permohonan reset PIN untuk nasabah {$pinReq->nama_siswa} ({$pinReq->no_rekening}) telah DISETUJUI. Nasabah sekarang dapat membuat 6 digit PIN baru secara mandiri.");
    }

    /**
     * Admin Menolak Permohonan Reset PIN
     */
    public function adminReject(Request $request, int|string $id): RedirectResponse
    {
        $pinReq = PinResetRequest::findOrFail($id);

        if ($pinReq->status !== 'pending') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $alasan = $request->input('alasan_penolakan', 'Verifikasi identitas nasabah tidak sesuai');

        $pinReq->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'alasan' => $alasan,
        ]);

        AuditHelper::log(
            jenisAktivitas: 'REJECT_RESET_PIN',
            modul: 'Nasabah',
            objekId: "#PIN-REQ-{$pinReq->id}",
            oldValues: ['status' => 'pending'],
            newValues: ['status' => 'rejected', 'alasan' => $alasan]
        );

        return back()->with('success', "Permohonan reset PIN untuk {$pinReq->nama_siswa} telah ditolak.");
    }

    // =========================================================================
    // ENDPOINT API UNTUK MOBILE NASABAH
    // =========================================================================

    public function apiRequestReset(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'alasan' => 'nullable|string|max:255',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)
            ->orWhere('nis', $request->no_rekening)
            ->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Akun nasabah tidak ditemukan!',
            ], 404);
        }

        $activeRequest = PinResetRequest::where('nasabah_id', $nasabah->id)
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();

        if ($activeRequest) {
            return response()->json([
                'success' => true,
                'status' => $activeRequest->status,
                'message' => $activeRequest->status === 'approved'
                    ? 'Permohonan telah disetujui Admin! Silakan masukkan PIN 6-digit baru Anda.'
                    : 'Permohonan reset PIN Anda masih menunggu persetujuan Admin.',
            ]);
        }

        $req = PinResetRequest::create([
            'nasabah_id' => $nasabah->id,
            'no_rekening' => $nasabah->no_rekening,
            'nis' => $nasabah->nis,
            'nama_siswa' => $nasabah->nama_siswa,
            'alasan' => $request->alasan ?: 'Lupa PIN aplikasi mobile',
            'status' => 'pending',
            'reset_token' => Str::random(40),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Permohonan lupa PIN berhasil diajukan ke Admin.',
            'data' => $req,
        ]);
    }

    public function apiCheckStatus(Request $request): JsonResponse
    {
        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)
            ->orWhere('nis', $request->no_rekening)
            ->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah tidak ditemukan!',
            ], 404);
        }

        $latestReq = PinResetRequest::where('nasabah_id', $nasabah->id)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'has_request' => (bool) $latestReq,
            'status' => $latestReq ? $latestReq->status : 'none',
            'can_set_pin' => $latestReq && $latestReq->status === 'approved',
            'data' => $latestReq,
        ]);
    }

    public function apiSetNewPin(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'pin_baru' => 'required|digits:6',
        ]);

        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)
            ->orWhere('nis', $request->no_rekening)
            ->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah tidak ditemukan!',
            ], 404);
        }

        $approvedRequest = PinResetRequest::where('nasabah_id', $nasabah->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (! $approvedRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Permohonan reset PIN belum disetujui Admin!',
            ], 403);
        }

        $nasabah->update([
            'pin_keamanan' => Hash::make($request->pin_baru),
        ]);

        $approvedRequest->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN 6-digit berhasil diubah secara mandiri!',
        ]);
    }
}
