<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class TransaksiNasabahController extends Controller
{
    // 1. TRANSFER SALDO ANTAR NASABAH (E-WALLET / MOBILE)
    public function transfer(Request $request): JsonResponse
    {
        $userNasabah = $this->resolveNasabah($request);

        $request->validate([
            'no_rekening_pengirim' => $userNasabah ? 'nullable|string' : 'required|string',
            'no_rekening_tujuan' => 'required|string',
            'nominal' => 'required|numeric|min:1000',
            'pin' => 'required|digits:6',
            'catatan' => 'nullable|string|max:100',
        ]);

        $noPengirim = $userNasabah ? $userNasabah->no_rekening : $request->no_rekening_pengirim;

        if ($noPengirim === $request->no_rekening_tujuan) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat melakukan transfer ke rekening sendiri!',
            ], 400);
        }

        $pengirim = $userNasabah ?: Nasabah::where('no_rekening', $noPengirim)->first();
        if (! $pengirim) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening pengirim tidak valid!',
            ], 404);
        }

        // Verifikasi PIN Pengirim
        if (! Hash::check($request->pin, $pengirim->pin_keamanan)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN Keamanan salah!',
            ], 400);
        }

        $penerima = Nasabah::where('no_rekening', $request->no_rekening_tujuan)->first();
        if (! $penerima) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tujuan tidak ditemukan!',
            ], 404);
        }

        $nominal = (int) $request->nominal;

        if ($pengirim->saldo < $nominal) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Anda tidak mencukupi untuk transfer ini!',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Kurangi saldo pengirim
            $pengirim->decrement('saldo', $nominal);

            // 2. Tambah saldo penerima
            $penerima->increment('saldo', $nominal);

            $catatan = $request->catatan ? ' ('.$request->catatan.')' : '';

            // 3. Catat mutasi keluar pada akun pengirim
            Transaksi::create([
                'nasabah_id' => $pengirim->id,
                'jenis_transaksi' => 'transfer_keluar',
                'nominal' => $nominal,
                'keterangan' => 'Transfer ke '.$penerima->nama_siswa.$catatan,
                'status' => 'sukses',
            ]);

            // 4. Catat mutasi masuk pada akun penerima
            Transaksi::create([
                'nasabah_id' => $penerima->id,
                'jenis_transaksi' => 'transfer_masuk',
                'nominal' => $nominal,
                'keterangan' => 'Transfer dari '.$pengirim->nama_siswa.$catatan,
                'status' => 'sukses',
            ]);

            DB::commit();

            $sisaSaldo = (float) $pengirim->saldo;

            return response()->json([
                'success' => true,
                'message' => 'Transfer sejumlah Rp '.number_format($nominal, 0, ',', '.').' ke '.$penerima->nama_siswa.' berhasil!',
                'data' => [
                    'nominal' => $nominal,
                    'no_rekening_tujuan' => $penerima->no_rekening,
                    'nama_penerima' => $penerima->nama_siswa,
                    'sisa_saldo' => (float) $sisaSaldo,
                    'waktu' => now()->format('Y-m-d H:i:s'),
                ],
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat transfer: '.$e->getMessage(),
            ], 500);
        }
    }

    // 2. RIWAYAT TRANSAKSI / MUTASI
    public function riwayat(Request $request): JsonResponse
    {
        $userNasabah = $this->resolveNasabah($request);
        $noRekening = $userNasabah ? $userNasabah->no_rekening : $request->query('no_rekening');
        $idNasabah = $userNasabah ? $userNasabah->id : $request->query('id_nasabah');
        $jenis = $request->query('jenis');

        $query = Transaksi::with('nasabah')
            ->when($noRekening, function ($q, $noRekening) {
                return $q->whereHas('nasabah', fn ($sub) => $sub->where('no_rekening', $noRekening));
            })
            ->when($idNasabah, function ($q, $idNasabah) {
                return $q->where('nasabah_id', $idNasabah);
            })
            ->when($jenis, function ($q, $jenis) {
                return $q->where('jenis_transaksi', $jenis);
            });

        $riwayat = $query->orderBy('id', 'desc')->get();

        $riwayat->each(function ($item) {
            $item->no_rekening = $item->nasabah->no_rekening ?? null;
            $item->nama_siswa = $item->nasabah->nama_siswa ?? null;
            $item->formatted_nominal = 'Rp '.number_format($item->nominal, 0, ',', '.');
        });

        return response()->json([
            'success' => true,
            'count' => $riwayat->count(),
            'data' => $riwayat,
        ]);
    }

    // 3. RINGKASAN PEMASUKAN & PENGELUARAN BULAN INI
    public function ringkasan(Request $request): JsonResponse
    {
        $nasabah = $this->resolveNasabah($request);

        if (! $nasabah) {
            return response()->json(['success' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        // Pemasukan bulan ini: setor + transfer_masuk
        $totalMasuk = Transaksi::where('nasabah_id', $nasabah->id)
            ->whereIn('jenis_transaksi', ['setor', 'transfer_masuk'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        // Pengeluaran bulan ini: tarik + transfer_keluar
        $totalKeluar = Transaksi::where('nasabah_id', $nasabah->id)
            ->whereIn('jenis_transaksi', ['tarik', 'transfer_keluar'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('nominal');

        return response()->json([
            'success' => true,
            'data' => [
                'no_rekening' => $nasabah->no_rekening,
                'nama_siswa' => $nasabah->nama_siswa,
                'saldo' => (float) $nasabah->saldo,
                'formatted_saldo' => 'Rp '.number_format($nasabah->saldo, 0, ',', '.'),
                'total_masuk' => (float) $totalMasuk,
                'formatted_masuk' => 'Rp '.number_format($totalMasuk, 0, ',', '.'),
                'total_keluar' => (float) $totalKeluar,
                'formatted_keluar' => 'Rp '.number_format($totalKeluar, 0, ',', '.'),
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

        $noRekening = $request->query('no_rekening') ?? $request->no_rekening ?? $request->no_rekening_pengirim;
        if ($noRekening) {
            return Nasabah::where('no_rekening', $noRekening)->first();
        }

        if ($request->query('id_nasabah')) {
            return Nasabah::find($request->query('id_nasabah'));
        }

        return null;
    }
}
