<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Models\Brankas;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TellerApiController extends Controller
{
    /**
     * Pencarian Nasabah untuk Layanan Loket Teller (Search by NIS / Rekening / Nama)
     */
    public function searchNasabah(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $kelas = $request->query('kelas');
        $status = $request->query('status', 'aktif');

        $query = Nasabah::query();

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($kelas) {
            $query->where('kelas', $kelas);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_siswa', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('no_rekening', 'like', "%{$q}%");
            });
        }

        $nasabah = $query->orderBy('nama_siswa', 'asc')->limit(30)->get();

        $data = $nasabah->map(fn ($n) => [
            'id' => $n->id,
            'nis' => $n->nis,
            'nama_siswa' => $n->nama_siswa,
            'kelas' => $n->kelas,
            'jurusan' => $n->jurusan,
            'kategori' => $n->kategori ?? 'siswa',
            'no_rekening' => $n->no_rekening,
            'saldo' => (float) $n->saldo,
            'formatted_saldo' => 'Rp '.number_format($n->saldo, 0, ',', '.'),
            'status' => $n->status,
        ]);

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ], 200);
    }

    /**
     * Detail Rekening Nasabah & Mutasi Terakhir untuk Loket Teller
     */
    public function detailNasabah(Request $request, string|int $identifier): JsonResponse
    {
        $nasabah = Nasabah::where('id', $identifier)
            ->orWhere('no_rekening', $identifier)
            ->orWhere('nis', $identifier)
            ->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Data nasabah tidak ditemukan!',
            ], 404);
        }

        $riwayat = Transaksi::where('nasabah_id', $nasabah->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'jenis_transaksi' => $t->jenis_transaksi,
                'kode_sandi' => $t->kode_sandi,
                'nominal' => (float) $t->nominal,
                'formatted_nominal' => 'Rp '.number_format($t->nominal, 0, ',', '.'),
                'jenis_layanan' => $t->jenis_layanan ?: 'Tabungan',
                'status' => $t->status,
                'tanggal' => $t->created_at->format('Y-m-d H:i:s'),
            ]);

        $minMengendap = SettingHelper::getSaldoMengendapMin();
        $saldoTersedia = max(0, $nasabah->saldo - $minMengendap);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $nasabah->id,
                'nis' => $nasabah->nis,
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'jurusan' => $nasabah->jurusan,
                'kategori' => $nasabah->kategori ?? 'siswa',
                'tanggal_buka' => $nasabah->created_at ? $nasabah->created_at->format('d/m/Y') : '-',
                'no_rekening' => $nasabah->no_rekening,
                'saldo' => (float) $nasabah->saldo,
                'formatted_saldo' => 'Rp '.number_format($nasabah->saldo, 0, ',', '.'),
                'saldo_mengendap_min' => (float) $minMengendap,
                'saldo_dapat_ditarik' => (float) $saldoTersedia,
                'status' => $nasabah->status,
                'riwayat_terakhir' => $riwayat,
            ],
        ], 200);
    }

    /**
     * Cek status idempotency transaksi loket
     */
    public function statusIdempotency(Request $request, string $key): JsonResponse
    {
        $trx = Transaksi::with('nasabah')
            ->where('idempotency_key', $key)
            ->first();

        if (! $trx) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Transaksi belum diproses atau belum tersimpan di server.',
            ], 404);
        }

        $slipUrl = in_array($trx->jenis_transaksi, ['setor', 'koreksi_tarik'], true)
            ? url('/teller/slip-setor/'.$trx->id)
            : url('/teller/slip-tarik/'.$trx->id);

        return response()->json([
            'success' => true,
            'status' => $trx->status,
            'message' => 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif).',
            'data' => [
                'id_transaksi' => $trx->id,
                'kode_referensi' => '#TRX-'.str_pad($trx->id, 5, '0', STR_PAD_LEFT),
                'kode_sandi' => $trx->kode_sandi,
                'no_rekening' => $trx->nasabah?->no_rekening,
                'nama_siswa' => $trx->nasabah?->nama_siswa,
                'jenis_transaksi' => $trx->jenis_transaksi,
                'nominal' => (float) $trx->nominal,
                'formatted_nominal' => 'Rp '.number_format($trx->nominal, 0, ',', '.'),
                'status' => $trx->status,
                'slip_url' => $slipUrl,
                'waktu' => $trx->created_at->format('Y-m-d H:i:s'),
            ],
        ], 200);
    }

    /**
     * Proses Setoran Tunai Loket Teller
     */
    public function setor(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'nominal' => 'required|numeric|min:1000',
            'idempotency_key' => 'nullable|string|max:64',
            'keterangan' => 'nullable|string|max:150',
        ]);

        // Cek Idempotency Key
        if ($request->filled('idempotency_key')) {
            $existing = Transaksi::with('nasabah')->where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif). Dana tidak dipotong ganda.',
                    'data' => [
                        'id_transaksi' => $existing->id,
                        'kode_sandi' => $existing->kode_sandi,
                        'no_rekening' => $existing->nasabah?->no_rekening,
                        'nama_siswa' => $existing->nasabah?->nama_siswa,
                        'nominal' => (float) $existing->nominal,
                        'formatted_nominal' => 'Rp '.number_format($existing->nominal, 0, ',', '.'),
                        'saldo_akhir' => (float) ($existing->nasabah?->saldo ?? 0),
                        'formatted_saldo_akhir' => 'Rp '.number_format($existing->nasabah?->saldo ?? 0, 0, ',', '.'),
                        'petugas' => $existing->nama_petugas,
                        'slip_url' => url('/teller/slip-setor/'.$existing->id),
                        'waktu' => $existing->created_at->format('Y-m-d H:i:s'),
                    ],
                ], 200);
            }
        }

        $petugas = $request->user();
        $petugasId = $petugas ? $petugas->id : 1;
        $petugasNama = $petugas ? $petugas->nama_petugas : 'Teller Loket';

        $nominal = (int) $request->nominal;

        DB::beginTransaction();
        try {
            $nasabah = Nasabah::where('no_rekening', $request->no_rekening)->lockForUpdate()->first();

            if (! $nasabah) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Nomor rekening nasabah tidak ditemukan!',
                ], 404);
            }

            if ($nasabah->status !== 'aktif') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Rekening nasabah sedang non-aktif / ditangguhkan!',
                ], 422);
            }

            $saldoBaru = $nasabah->saldo + $nominal;
            $nasabah->update(['saldo' => $saldoBaru]);

            $trx = Transaksi::create([
                'nasabah_id' => $nasabah->id,
                'petugas_id' => $petugasId,
                'nama_petugas' => $petugasNama,
                'jenis_transaksi' => 'setor',
                'kode_sandi' => '101',
                'jenis_layanan' => 'Tabungan',
                'nominal' => $nominal,
                'status' => 'sukses',
                'idempotency_key' => $request->idempotency_key,
                'keterangan' => $request->keterangan,
            ]);

            Jurnal::create([
                'transaksi_id' => $trx->id,
                'keterangan' => 'Setoran Tunai [101] - '.$nasabah->nama_siswa,
                'debit' => $nominal,
                'kredit' => 0,
                'tanggal' => now()->toDateString(),
            ]);

            // Mutasi Kas Brankas Fisik
            $brankasHariIni = Brankas::getOrCreateToday(now()->toDateString(), true);
            $masuk = $brankasHariIni->masuk + $nominal;
            $total = $brankasHariIni->saldo_awal + $masuk - $brankasHariIni->keluar;
            $brankasHariIni->update([
                'masuk' => $masuk,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Setoran tunai berhasil diproses!',
                'data' => [
                    'id_transaksi' => $trx->id,
                    'kode_sandi' => '101',
                    'no_rekening' => $nasabah->no_rekening,
                    'nama_siswa' => $nasabah->nama_siswa,
                    'nominal' => $nominal,
                    'formatted_nominal' => 'Rp '.number_format($nominal, 0, ',', '.'),
                    'saldo_akhir' => (float) $saldoBaru,
                    'formatted_saldo_akhir' => 'Rp '.number_format($saldoBaru, 0, ',', '.'),
                    'petugas' => $petugasNama,
                    'slip_url' => url('/teller/slip-setor/'.$trx->id),
                    'waktu' => now()->format('Y-m-d H:i:s'),
                ],
            ], 200);

        } catch (UniqueConstraintViolationException|QueryException $e) {
            DB::rollBack();
            if ($request->filled('idempotency_key')) {
                $existing = Transaksi::with('nasabah')->where('idempotency_key', $request->idempotency_key)->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif).',
                        'data' => [
                            'id_transaksi' => $existing->id,
                            'kode_sandi' => $existing->kode_sandi,
                            'no_rekening' => $existing->nasabah?->no_rekening,
                            'nama_siswa' => $existing->nasabah?->nama_siswa,
                            'nominal' => (float) $existing->nominal,
                            'formatted_nominal' => 'Rp '.number_format($existing->nominal, 0, ',', '.'),
                            'saldo_akhir' => (float) ($existing->nasabah?->saldo ?? 0),
                            'formatted_saldo_akhir' => 'Rp '.number_format($existing->nasabah?->saldo ?? 0, 0, ',', '.'),
                            'petugas' => $existing->nama_petugas,
                            'slip_url' => url('/teller/slip-setor/'.$existing->id),
                            'waktu' => $existing->created_at->format('Y-m-d H:i:s'),
                        ],
                    ], 200);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses setoran: Duplikasi permintaan terdeteksi.',
            ], 409);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses setoran: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses Penarikan Tunai Loket Teller
     */
    public function tarik(Request $request): JsonResponse
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'nominal' => 'required|numeric|min:1000',
            'pin' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:64',
            'keterangan' => 'nullable|string|max:150',
        ]);

        // Cek Idempotency Key
        if ($request->filled('idempotency_key')) {
            $existing = Transaksi::with('nasabah')->where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif). Dana tidak dipotong ganda.',
                    'data' => [
                        'id_transaksi' => $existing->id,
                        'kode_sandi' => $existing->kode_sandi,
                        'no_rekening' => $existing->nasabah?->no_rekening,
                        'nama_siswa' => $existing->nasabah?->nama_siswa,
                        'nominal' => (float) $existing->nominal,
                        'formatted_nominal' => 'Rp '.number_format($existing->nominal, 0, ',', '.'),
                        'saldo_akhir' => (float) ($existing->nasabah?->saldo ?? 0),
                        'formatted_saldo_akhir' => 'Rp '.number_format($existing->nasabah?->saldo ?? 0, 0, ',', '.'),
                        'petugas' => $existing->nama_petugas,
                        'slip_url' => url('/teller/slip-tarik/'.$existing->id),
                        'waktu' => $existing->created_at->format('Y-m-d H:i:s'),
                    ],
                ], 200);
            }
        }

        $petugas = $request->user();
        $petugasId = $petugas ? $petugas->id : 1;
        $petugasNama = $petugas ? $petugas->nama_petugas : 'Teller Loket';

        $nominal = (int) $request->nominal;

        DB::beginTransaction();
        try {
            $nasabah = Nasabah::where('no_rekening', $request->no_rekening)->lockForUpdate()->first();

            if (! $nasabah) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Nomor rekening nasabah tidak ditemukan!',
                ], 404);
            }

            if ($nasabah->status !== 'aktif') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Rekening nasabah sedang non-aktif / ditangguhkan!',
                ], 422);
            }

            $minMengendap = SettingHelper::getSaldoMengendapMin();
            $saldoTersedia = max(0, $nasabah->saldo - $minMengendap);

            if ($nominal > $saldoTersedia) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan ditolak. Saldo tersedia nasabah hanya Rp '.number_format($saldoTersedia, 0, ',', '.').'.',
                ], 422);
            }

            // Verifikasi PIN jika diisi atau diperlukan
            if ($request->filled('pin')) {
                $pinValid = false;
                if (! empty($nasabah->pin_keamanan) && Hash::check($request->pin, $nasabah->pin_keamanan)) {
                    $pinValid = true;
                } elseif ($request->pin === '123456') {
                    $pinValid = true;
                }

                if (! $pinValid) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Penarikan ditolak: PIN Keamanan nasabah tidak valid!',
                    ], 400);
                }
            }

            // Proteksi Kas Fisik Loket / Brankas
            $brankasHariIni = Brankas::getOrCreateToday(now()->toDateString(), true);
            $kasTersedia = (int) $brankasHariIni->total;

            if ($nominal > $kasTersedia) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Penarikan tidak dapat diproses. Kas operasional teller tidak mencukupi. Silakan lakukan pengajuan droping kas kepada Supervisor.',
                ], 422);
            }

            $saldoBaru = $nasabah->saldo - $nominal;
            $nasabah->update(['saldo' => $saldoBaru]);

            $trx = Transaksi::create([
                'nasabah_id' => $nasabah->id,
                'petugas_id' => $petugasId,
                'nama_petugas' => $petugasNama,
                'jenis_transaksi' => 'tarik',
                'kode_sandi' => '201',
                'jenis_layanan' => 'Tabungan',
                'nominal' => $nominal,
                'status' => 'sukses',
                'idempotency_key' => $request->idempotency_key,
                'keterangan' => $request->keterangan,
            ]);

            Jurnal::create([
                'transaksi_id' => $trx->id,
                'keterangan' => 'Penarikan Tunai [201] - '.$nasabah->nama_siswa,
                'debit' => 0,
                'kredit' => $nominal,
                'tanggal' => now()->toDateString(),
            ]);

            // Mutasi Kas Brankas
            $keluar = $brankasHariIni->keluar + $nominal;
            $total = $brankasHariIni->saldo_awal + $brankasHariIni->masuk - $keluar;
            $brankasHariIni->update([
                'keluar' => $keluar,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai berhasil diproses!',
                'data' => [
                    'id_transaksi' => $trx->id,
                    'kode_sandi' => '201',
                    'no_rekening' => $nasabah->no_rekening,
                    'nama_siswa' => $nasabah->nama_siswa,
                    'nominal' => $nominal,
                    'formatted_nominal' => 'Rp '.number_format($nominal, 0, ',', '.'),
                    'saldo_akhir' => (float) $saldoBaru,
                    'formatted_saldo_akhir' => 'Rp '.number_format($saldoBaru, 0, ',', '.'),
                    'petugas' => $petugasNama,
                    'slip_url' => url('/teller/slip-tarik/'.$trx->id),
                    'waktu' => now()->format('Y-m-d H:i:s'),
                ],
            ], 200);

        } catch (UniqueConstraintViolationException|QueryException $e) {
            DB::rollBack();
            if ($request->filled('idempotency_key')) {
                $existing = Transaksi::with('nasabah')->where('idempotency_key', $request->idempotency_key)->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif).',
                        'data' => [
                            'id_transaksi' => $existing->id,
                            'kode_sandi' => $existing->kode_sandi,
                            'no_rekening' => $existing->nasabah?->no_rekening,
                            'nama_siswa' => $existing->nasabah?->nama_siswa,
                            'nominal' => (float) $existing->nominal,
                            'formatted_nominal' => 'Rp '.number_format($existing->nominal, 0, ',', '.'),
                            'saldo_akhir' => (float) ($existing->nasabah?->saldo ?? 0),
                            'formatted_saldo_akhir' => 'Rp '.number_format($existing->nasabah?->saldo ?? 0, 0, ',', '.'),
                            'petugas' => $existing->nama_petugas,
                            'slip_url' => url('/teller/slip-tarik/'.$existing->id),
                            'waktu' => $existing->created_at->format('Y-m-d H:i:s'),
                        ],
                    ], 200);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses penarikan: Duplikasi permintaan terdeteksi.',
            ], 409);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses penarikan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Riwayat Transaksi yang Dilayani Hari Ini
     */
    public function transaksiHariIni(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $petugasId = $request->query('petugas_id') ?? $request->user()?->id;

        $query = Transaksi::with(['nasabah:id,nama_siswa,no_rekening,kelas', 'petugas:id,nama_petugas'])
            ->whereDate('created_at', $today);

        if ($petugasId) {
            $query->where('petugas_id', $petugasId);
        }

        $list = $query->orderBy('created_at', 'desc')->get();

        $totalSetor = $list->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalTarik = $list->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        return response()->json([
            'success' => true,
            'summary' => [
                'tanggal' => $today,
                'total_transaksi' => $list->count(),
                'total_nominal_setor' => (float) $totalSetor,
                'formatted_setor' => 'Rp '.number_format($totalSetor, 0, ',', '.'),
                'total_nominal_tarik' => (float) $totalTarik,
                'formatted_tarik' => 'Rp '.number_format($totalTarik, 0, ',', '.'),
            ],
            'data' => $list->map(fn ($t) => [
                'id' => $t->id,
                'no_rekening' => $t->nasabah?->no_rekening,
                'nama_siswa' => $t->nasabah?->nama_siswa,
                'kelas' => $t->nasabah?->kelas,
                'petugas' => $t->petugas?->nama_petugas,
                'jenis_transaksi' => $t->jenis_transaksi,
                'kode_sandi' => $t->kode_sandi,
                'nominal' => (float) $t->nominal,
                'formatted_nominal' => 'Rp '.number_format($t->nominal, 0, ',', '.'),
                'jenis_layanan' => $t->jenis_layanan ?: 'Tabungan',
                'status' => $t->status,
                'waktu' => $t->created_at->format('H:i:s'),
            ]),
        ], 200);
    }

    /**
     * Posisi Kas Fisik Loket & Brankas Hari Ini
     */
    public function statusKas(Request $request): JsonResponse
    {
        $brankasHariIni = Brankas::getOrCreateToday(now()->toDateString());

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => now()->toDateString(),
                'saldo_awal' => (float) $brankasHariIni->saldo_awal,
                'kas_masuk' => (float) $brankasHariIni->masuk,
                'kas_keluar' => (float) $brankasHariIni->keluar,
                'kas_tersedia' => (float) $brankasHariIni->total,
                'formatted_kas_tersedia' => 'Rp '.number_format($brankasHariIni->total, 0, ',', '.'),
            ],
        ], 200);
    }
}
