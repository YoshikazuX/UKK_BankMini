<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\SavingGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SavingGoalApiController extends Controller
{
    /**
     * Resolusi entitas Nasabah dari sesi Sanctum aktif.
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

        if ($request->query('no_rekening')) {
            return Nasabah::where('no_rekening', $request->query('no_rekening'))->first();
        }

        return null;
    }

    /**
     * Daftar seluruh target tabungan nasabah.
     */
    public function index(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);
        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $goals = SavingGoal::where('nasabah_id', $nasabah->id)
            ->orderBy('is_completed', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn (SavingGoal $g) => [
                'id' => (string) $g->id,
                'nama' => $g->nama,
                'target_amount' => (float) $g->target_amount,
                'formatted_target_amount' => 'Rp '.number_format($g->target_amount, 0, ',', '.'),
                'current_amount' => (float) $g->current_amount,
                'formatted_current_amount' => 'Rp '.number_format($g->current_amount, 0, ',', '.'),
                'icon' => $g->icon ?? 'savings',
                'created_at' => $g->created_at?->format('Y-m-d H:i:s'),
                'deadline' => $g->deadline ? $g->deadline->format('Y-m-d') : null,
                'is_completed' => (bool) $g->is_completed,
                'progress_percent' => $g->progress_percent,
                'remaining' => $g->remaining,
                'formatted_remaining' => 'Rp '.number_format($g->remaining, 0, ',', '.'),
            ]);

        $totalTeralokasi = (float) SavingGoal::where('nasabah_id', $nasabah->id)->sum('current_amount');
        $saldoRekening = (float) $nasabah->saldo;
        $saldoTersedia = max(0.0, $saldoRekening - $totalTeralokasi);

        return response()->json([
            'success' => true,
            'message' => 'Daftar target tabungan berhasil dimuat',
            'count' => $goals->count(),
            'total_saldo_rekening' => $saldoRekening,
            'formatted_saldo_rekening' => 'Rp '.number_format($saldoRekening, 0, ',', '.'),
            'total_teralokasi' => $totalTeralokasi,
            'formatted_total_teralokasi' => 'Rp '.number_format($totalTeralokasi, 0, ',', '.'),
            'saldo_tersedia' => $saldoTersedia,
            'formatted_saldo_tersedia' => 'Rp '.number_format($saldoTersedia, 0, ',', '.'),
            'data' => $goals,
        ], 200);
    }

    /**
     * Buat target tabungan impian baru.
     */
    public function store(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);
        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'target_amount' => 'required|numeric|min:1000',
            'current_amount' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
        ]);

        $currentAmount = isset($validated['current_amount']) ? (float) $validated['current_amount'] : 0.0;
        $targetAmount = (float) $validated['target_amount'];

        $totalAllocated = (float) SavingGoal::where('nasabah_id', $nasabah->id)->sum('current_amount');
        $availableSaldo = max(0.0, (float) $nasabah->saldo - $totalAllocated);

        if ($currentAmount > $availableSaldo) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal awal melebihi saldo rekening yang tersedia untuk disisihkan. Sisa saldo tersedia: Rp '.number_format($availableSaldo, 0, ',', '.'),
            ], 422);
        }

        if ($currentAmount > $targetAmount) {
            $currentAmount = $targetAmount;
        }

        $isCompleted = $currentAmount >= $targetAmount;

        $goal = SavingGoal::create([
            'nasabah_id' => $nasabah->id,
            'nama' => $validated['nama'],
            'target_amount' => $targetAmount,
            'current_amount' => $currentAmount,
            'icon' => $validated['icon'] ?? 'savings',
            'deadline' => $validated['deadline'] ?? null,
            'is_completed' => $isCompleted,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan "'.$goal->nama.'" berhasil dibuat!',
            'data' => [
                'id' => (string) $goal->id,
                'nama' => $goal->nama,
                'target_amount' => (float) $goal->target_amount,
                'formatted_target_amount' => 'Rp '.number_format($goal->target_amount, 0, ',', '.'),
                'current_amount' => (float) $goal->current_amount,
                'formatted_current_amount' => 'Rp '.number_format($goal->current_amount, 0, ',', '.'),
                'icon' => $goal->icon,
                'deadline' => $goal->deadline ? $goal->deadline->format('Y-m-d') : null,
                'is_completed' => (bool) $goal->is_completed,
                'progress_percent' => $goal->progress_percent,
                'remaining' => $goal->remaining,
            ],
        ], 201);
    }

    /**
     * Detail target tabungan.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);
        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $goal = SavingGoal::where('nasabah_id', $nasabah->id)->find($id);
        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $goal->id,
                'nama' => $goal->nama,
                'target_amount' => (float) $goal->target_amount,
                'formatted_target_amount' => 'Rp '.number_format($goal->target_amount, 0, ',', '.'),
                'current_amount' => (float) $goal->current_amount,
                'formatted_current_amount' => 'Rp '.number_format($goal->current_amount, 0, ',', '.'),
                'icon' => $goal->icon,
                'deadline' => $goal->deadline ? $goal->deadline->format('Y-m-d') : null,
                'is_completed' => (bool) $goal->is_completed,
                'progress_percent' => $goal->progress_percent,
                'remaining' => $goal->remaining,
            ],
        ], 200);
    }

    /**
     * Perbarui target tabungan.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);
        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $goal = SavingGoal::where('nasabah_id', $nasabah->id)->find($id);
        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:150',
            'target_amount' => 'sometimes|required|numeric|min:1000',
            'current_amount' => 'sometimes|nullable|numeric|min:0',
            'icon' => 'sometimes|nullable|string|max:50',
            'deadline' => 'sometimes|nullable|date',
            'is_completed' => 'sometimes|nullable|boolean',
        ]);

        $goal->fill($validated);
        if ($goal->current_amount >= $goal->target_amount) {
            $goal->is_completed = true;
        }
        $goal->save();

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil diperbarui!',
            'data' => [
                'id' => (string) $goal->id,
                'nama' => $goal->nama,
                'target_amount' => (float) $goal->target_amount,
                'formatted_target_amount' => 'Rp '.number_format($goal->target_amount, 0, ',', '.'),
                'current_amount' => (float) $goal->current_amount,
                'formatted_current_amount' => 'Rp '.number_format($goal->current_amount, 0, ',', '.'),
                'icon' => $goal->icon,
                'deadline' => $goal->deadline ? $goal->deadline->format('Y-m-d') : null,
                'is_completed' => (bool) $goal->is_completed,
                'progress_percent' => $goal->progress_percent,
                'remaining' => $goal->remaining,
            ],
        ], 200);
    }

    /**
     * Alokasikan dana dari saldo rekening ke target tabungan impian.
     * Saldo rekening di dashboard tetap utuh/sama, dana dicatat tersisihkan untuk target tabungan.
     */
    public function alokasiDana(Request $request, int|string $id): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);
        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $goal = SavingGoal::where('nasabah_id', $nasabah->id)->find($id);
        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'nominal' => 'required|numeric|min:0',
            'mode' => 'nullable|string|in:tambah,set,kurang',
        ]);

        $nominal = (float) $validated['nominal'];
        $mode = $validated['mode'] ?? 'tambah';

        $totalAllocatedOther = (float) SavingGoal::where('nasabah_id', $nasabah->id)
            ->where('id', '!=', $goal->id)
            ->sum('current_amount');

        $saldoNasabah = (float) $nasabah->saldo;
        $maxBisaDialokasikan = max(0.0, $saldoNasabah - $totalAllocatedOther);

        if ($mode === 'tambah') {
            $newAmount = (float) $goal->current_amount + $nominal;
        } elseif ($mode === 'kurang') {
            $newAmount = max(0.0, (float) $goal->current_amount - $nominal);
        } else { // 'set'
            $newAmount = $nominal;
        }

        if ($newAmount > $goal->target_amount) {
            $newAmount = (float) $goal->target_amount;
        }

        if ($newAmount > $maxBisaDialokasikan) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo rekening tidak mencukupi untuk disisihkan. Sisa saldo rekening yang tersedia untuk disisihkan: Rp '.number_format($maxBisaDialokasikan, 0, ',', '.'),
                'saldo_tersedia' => $maxBisaDialokasikan,
                'saldo_rekening' => $saldoNasabah,
            ], 422);
        }

        $goal->current_amount = $newAmount;
        $goal->is_completed = ($newAmount >= $goal->target_amount);
        $goal->save();

        $sisaTersedia = max(0.0, $saldoNasabah - ($totalAllocatedOther + $goal->current_amount));

        return response()->json([
            'success' => true,
            'message' => 'Alokasi tabungan untuk target "'.$goal->nama.'" berhasil diperbarui sebesar Rp '.number_format($newAmount, 0, ',', '.').'!',
            'data' => [
                'id' => (string) $goal->id,
                'nama' => $goal->nama,
                'target_amount' => (float) $goal->target_amount,
                'formatted_target_amount' => 'Rp '.number_format($goal->target_amount, 0, ',', '.'),
                'current_amount' => (float) $goal->current_amount,
                'formatted_current_amount' => 'Rp '.number_format($goal->current_amount, 0, ',', '.'),
                'icon' => $goal->icon,
                'deadline' => $goal->deadline ? $goal->deadline->format('Y-m-d') : null,
                'is_completed' => (bool) $goal->is_completed,
                'progress_percent' => $goal->progress_percent,
                'remaining' => $goal->remaining,
                'formatted_remaining' => 'Rp '.number_format($goal->remaining, 0, ',', '.'),
                'saldo_rekening' => $saldoNasabah,
                'saldo_tersedia' => $sisaTersedia,
            ],
        ], 200);
    }

    /**
     * Hapus target tabungan.
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);
        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $goal = SavingGoal::where('nasabah_id', $nasabah->id)->find($id);
        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan',
            ], 404);
        }

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil dihapus!',
        ], 200);
    }
}
