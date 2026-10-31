<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\AvalisSyncHelper;
use App\Helpers\SettingHelper;
use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\DashboardWidgetService;
use App\Services\ProfileImageService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TellerController extends Controller
{
    public function index(Request $request, DashboardWidgetService $widgetService): View
    {
        $katakunci = $request->cari;

        $query = Nasabah::query();
        if ($katakunci) {
            $query->where(function ($sub) use ($katakunci) {
                $sub->where('nis', 'LIKE', '%'.$katakunci.'%')
                    ->orWhere('no_rekening', 'LIKE', '%'.$katakunci.'%')
                    ->orWhere('nama_siswa', 'LIKE', '%'.$katakunci.'%');
            });
        }

        $nasabah = $query->get();

        // ALGORITMA SORTING PINTAR (Tingkat -> Jurusan -> SubKelas -> NIS)
        $nasabah = $nasabah->sortBy(function ($item) {
            $k = strtoupper($item->kelas);

            // 1. Deteksi Tingkat (Grade)
            $grade = 99;
            if (preg_match('/\bXII\b/', $k)) {
                $grade = 3;
            } elseif (preg_match('/\bXI\b/', $k)) {
                $grade = 2;
            } elseif (preg_match('/\bX\b/', $k)) {
                $grade = 1;
            }

            // 2. Deteksi Jurusan
            $jurusan = 99;
            if (preg_match('/\bRPL\b/', $k)) {
                $jurusan = 1;
            } elseif (preg_match('/\bTKJ\b/', $k)) {
                $jurusan = 2;
            } elseif (preg_match('/\bDKV\b/', $k)) {
                $jurusan = 3;
            } elseif (preg_match('/\bAK\b/', $k)) {
                $jurusan = 4;
            } elseif (preg_match('/\bLPS\b/', $k)) {
                $jurusan = 5;
            } elseif (preg_match('/\bMP\b/', $k)) {
                $jurusan = 6;
            } elseif (preg_match('/\bBR\b/', $k)) {
                $jurusan = 7;
            } elseif (preg_match('/\bBD\b/', $k)) {
                $jurusan = 8;
            }

            // 3. Deteksi Sub-kelas
            $sub = 99;
            if (preg_match('/\b(?:4|IV)\b/', $k)) {
                $sub = 4;
            } elseif (preg_match('/\b(?:3|III)\b/', $k)) {
                $sub = 3;
            } elseif (preg_match('/\b(?:2|II)\b/', $k)) {
                $sub = 2;
            } elseif (preg_match('/\b(?:1|I)\b/', $k)) {
                $sub = 1;
            }

            return sprintf('%02d-%02d-%02d-%s', $grade, $jurusan, $sub, $item->nis);
        })->values();

        $today = Carbon::today()->toDateString();
        $brankasHariIni = Brankas::getOrCreateToday($today);
        $kasTersedia = $brankasHariIni ? (int) $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? (int) $brankasHariIni->saldo_awal : 0;
        $pendingPengajuan = PengajuanKas::where('user_id', Auth::id())->where('status', 'pending')->count();
        $absensiSummary = AvalisSyncHelper::getDashboardSummary($today);

        // Arus Kas Operasional Loket Hari Ini
        $kasMasuk = Transaksi::where('petugas_id', Auth::id())
            ->whereDate('created_at', $today)
            ->where('jenis_transaksi', 'setor')
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $kasKeluar = Transaksi::where('petugas_id', Auth::id())
            ->whereDate('created_at', $today)
            ->whereIn('jenis_transaksi', ['tarik', 'admin'])
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        // Layanan Metrik & Peringatan Khusus Teller
        $nasabahSummary = $widgetService->getNasabahSummary();
        $pegawaiSummary = $widgetService->getPegawaiSummary();
        $tellerActivities = $widgetService->getTellerRecentActivities((int) Auth::id(), 5);
        $tellerAlerts = $widgetService->getTellerOperationalAlerts(Auth::user());

        return view('teller.dashboard', compact(
            'nasabah',
            'kasTersedia',
            'kasAwal',
            'kasMasuk',
            'kasKeluar',
            'pendingPengajuan',
            'absensiSummary',
            'nasabahSummary',
            'pegawaiSummary',
            'tellerActivities',
            'tellerAlerts'
        ));
    }

    public function cariByNis(string $nis): RedirectResponse
    {
        $nasabah = Nasabah::where('nis', $nis)
            ->orWhere('no_rekening', $nis)
            ->first();

        if ($nasabah) {
            return redirect('/teller/transaksi/'.$nasabah->id);
        }

        return redirect('/teller/dashboard')->with('error', 'Nasabah dengan NIS atau Nomor Rekening '.$nis.' tidak ditemukan!');
    }

    public function formTransaksi(int|string $id): View|RedirectResponse
    {
        $nasabah = Nasabah::where('id', $id)
            ->orWhere('no_rekening', $id)
            ->orWhere('nis', $id)
            ->first();

        if (! $nasabah) {
            return redirect('/teller/dashboard')->with('error', 'Data nasabah tidak ditemukan!');
        }

        $today = Carbon::today()->toDateString();
        $brankasHariIni = Brankas::getOrCreateToday($today);
        $kasTersedia = $brankasHariIni ? (int) $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? (int) $brankasHariIni->saldo_awal : 0;

        $minSaldoMengendap = SettingHelper::getSaldoMengendapMin();
        $saldoTersedia = max(0, $nasabah->saldo - $minSaldoMengendap);

        $riwayatTransaksi = Transaksi::where('nasabah_id', $nasabah->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('teller.transaksi', compact(
            'nasabah',
            'kasTersedia',
            'kasAwal',
            'minSaldoMengendap',
            'saldoTersedia',
            'riwayatTransaksi'
        ));
    }

    /**
     * Cek status transaksi berdasarkan idempotency key jika jaringan terputus
     */
    public function statusIdempotency(string $key): JsonResponse
    {
        $trx = Transaksi::with('nasabah')
            ->where('idempotency_key', $key)
            ->first();

        if (! $trx) {
            return response()->json([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Transaksi dengan kunci idempotensi ini belum tersimpan di server. Anda dapat melanjutkan proses pengiriman.',
            ], 404);
        }

        $slipUrl = in_array($trx->jenis_transaksi, ['setor', 'koreksi_tarik'], true)
            ? url('/teller/slip-setor/'.$trx->id)
            : url('/teller/slip-tarik/'.$trx->id);

        return response()->json([
            'success' => true,
            'status' => $trx->status,
            'message' => 'Transaksi telah berhasil diproses di server.',
            'data' => [
                'id' => $trx->id,
                'kode_referensi' => '#TRX-'.str_pad($trx->id, 5, '0', STR_PAD_LEFT),
                'status' => $trx->status,
                'jenis_transaksi' => $trx->jenis_transaksi,
                'kode_sandi' => $trx->kode_sandi,
                'nominal' => (int) $trx->nominal,
                'formatted_nominal' => 'Rp '.number_format($trx->nominal, 0, ',', '.'),
                'no_rekening' => $trx->nasabah?->no_rekening,
                'nama_nasabah' => $trx->nasabah?->nama_siswa,
                'slip_url' => $slipUrl,
                'waktu' => $trx->created_at->format('d/m/Y H:i:s'),
            ],
        ], 200);
    }

    public function prosesTransaksi(Request $request): RedirectResponse
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'jenis_transaksi' => 'required|in:setor,tarik,admin,koreksi_setor,koreksi_tarik',
            'nominal' => 'required|numeric|min:1000',
            'idempotency_key' => 'nullable|string|max:64',
            'alasan_koreksi' => 'nullable|string|max:255',
            'pin_nasabah' => 'nullable|string|max:10',
        ]);

        // 1. Mitigasi Teknis: Cek Idempotency Key (Mencegah double-submit jika koneksi terputus / klik ganda)
        if ($request->filled('idempotency_key')) {
            $existing = Transaksi::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                $slipUrl = in_array($existing->jenis_transaksi, ['setor', 'koreksi_tarik'], true)
                    ? url('/teller/slip-setor/'.$existing->id)
                    : url('/teller/slip-tarik/'.$existing->id);

                return redirect('/teller/dashboard')
                    ->with('success', 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif). Dana tidak dipotong ganda. Ref: #TRX-'.str_pad($existing->id, 5, '0', STR_PAD_LEFT))
                    ->with('slip_url', $slipUrl)
                    ->with('slip_jenis', $existing->jenis_transaksi);
            }
        }

        $nasabah_id = $request->nasabah_id;
        $jenis = $request->jenis_transaksi;
        $nominal = (int) $request->nominal;
        $petugas_id = Auth::id();
        $petugasNama = Auth::user()->nama_petugas ?? 'Teller';

        // 2. Kode Sandi Transaksi Standar Perbankan
        $kode_sandi = match ($jenis) {
            'setor' => '101',
            'tarik' => '201',
            'koreksi_setor' => '102',
            'koreksi_tarik' => '202',
            'admin' => '301',
            default => '100'
        };

        DB::beginTransaction();
        try {
            /** @var Nasabah|null $nasabah */
            $nasabah = Nasabah::where('id', $nasabah_id)->lockForUpdate()->first();
            if (! $nasabah) {
                DB::rollBack();

                return back()->with('error', 'Data nasabah tidak ditemukan.');
            }

            // Validasi Status Rekening
            if ($nasabah->status !== 'aktif') {
                DB::rollBack();

                return back()->with('error', 'Transaksi Ditolak: Rekening nasabah berstatus non-aktif / ditangguhkan. Operasional transaksi tidak diizinkan!');
            }

            $minSaldoMengendap = SettingHelper::getSaldoMengendapMin();
            $saldoTersedia = max(0, $nasabah->saldo - $minSaldoMengendap);

            // Aturan Saldo Mengendap Minimal Perbankan Mini untuk Penarikan
            if ($jenis === 'tarik') {
                if ($nominal > $saldoTersedia) {
                    DB::rollBack();
                    $formattedTersedia = number_format($saldoTersedia, 0, ',', '.');

                    return back()->with('error', "Penarikan ditolak. Saldo tersedia nasabah hanya Rp {$formattedTersedia}.");
                }

                // Otorisasi Form Verifikasi PIN Keamanan Nasabah (Wajib Sesuai Standar SOP Penarikan)
                $pinNasabah = $request->pin_nasabah ?? $request->pin_keamanan;
                if (empty($pinNasabah)) {
                    DB::rollBack();

                    return back()->with('error', 'Penarikan ditolak: Otorisasi PIN 6 digit nasabah wajib diisi untuk verifikasi penarikan tunai!');
                }

                $pinValid = false;
                if (! empty($nasabah->pin_keamanan) && Hash::check($pinNasabah, $nasabah->pin_keamanan)) {
                    $pinValid = true;
                } elseif ($pinNasabah === '123456') { // PIN Default Nasabah
                    $pinValid = true;
                }

                if (! $pinValid) {
                    DB::rollBack();

                    return back()->with('error', 'Penarikan ditolak: Otorisasi PIN 6 digit nasabah SALAH! Pastikan nasabah memasukkan PIN kartu yang benar.');
                }
            }

            // Aturan Saldo Cukup untuk Koreksi Setoran (Pembatalan Uang Palsu/Robek)
            if ($jenis === 'koreksi_setor' && $nasabah->saldo < $nominal) {
                DB::rollBack();

                return back()->with('error', 'Gagal! Saldo nasabah (Rp '.number_format($nasabah->saldo, 0, ',', '.').') tidak mencukupi untuk dilakukan koreksi setoran.');
            }

            // Aturan Potongan Admin
            if ($jenis === 'admin' && $nasabah->saldo < $nominal) {
                DB::rollBack();

                return back()->with('error', 'Gagal! Saldo nasabah (Rp '.number_format($nasabah->saldo, 0, ',', '.').') tidak cukup untuk dipotong biaya administrasi.');
            }

            // 3. PROTEKSI KAS MINUS PERBANKAN: KAS FISIK DI LOKET/BRANKAS DILARANG MINUS
            $brankasHariIni = Brankas::getOrCreateToday(now()->toDateString(), true);
            $kasTersedia = (int) $brankasHariIni->total;

            if (in_array($jenis, ['tarik', 'koreksi_setor'], true) && $nominal > $kasTersedia) {
                DB::rollBack();

                return back()
                    ->with('error', 'Penarikan tidak dapat diproses. Kas operasional teller tidak mencukupi. Silakan lakukan pengajuan droping kas kepada Supervisor.')
                    ->with('kas_habis', true)
                    ->with('kas_tersedia', $kasTersedia);
            }

            $trx = Transaksi::create([
                'nasabah_id' => $nasabah_id,
                'petugas_id' => $petugas_id,
                'nama_petugas' => $petugasNama,
                'jenis_layanan' => 'Tabungan',
                'jenis_transaksi' => $jenis,
                'kode_sandi' => $kode_sandi,
                'nominal' => $nominal,
                'status' => 'sukses',
                'idempotency_key' => $request->idempotency_key,
                'alasan_batal' => $request->alasan_koreksi,
            ]);

            // Hitung Saldo Baru
            if (in_array($jenis, ['setor', 'koreksi_tarik'], true)) {
                $saldo_baru = $nasabah->saldo + $nominal;
            } else {
                $saldo_baru = $nasabah->saldo - $nominal;
            }

            $nasabah->update(['saldo' => $saldo_baru]);

            // Pembukuan Jurnal Akuntansi
            $ket = match ($jenis) {
                'setor' => 'Setoran Tunai [101] - '.$nasabah->nama_siswa,
                'tarik' => 'Penarikan Tunai [201] - '.$nasabah->nama_siswa,
                'koreksi_setor' => 'KOREKSI SETOR [102] ('.($request->alasan_koreksi ?? 'Uang palsu/robek').') - '.$nasabah->nama_siswa,
                'koreksi_tarik' => 'KOREKSI TARIK [202] ('.($request->alasan_koreksi ?? 'Pembatalan salah tarik').') - '.$nasabah->nama_siswa,
                'admin' => 'Biaya Administrasi Buku [301] - '.$nasabah->nama_siswa,
            };

            $isDebit = in_array($jenis, ['setor', 'koreksi_tarik'], true);
            Jurnal::create([
                'transaksi_id' => $trx->id,
                'keterangan' => $ket,
                'debit' => $isDebit ? $nominal : 0,
                'kredit' => ! $isDebit ? $nominal : 0,
                'tanggal' => now()->toDateString(),
            ]);

            // Mutasi Kas Brankas Fisik
            $nominalMasuk = in_array($jenis, ['setor', 'koreksi_tarik'], true) ? $nominal : 0;
            $nominalKeluar = in_array($jenis, ['tarik', 'admin', 'koreksi_setor'], true) ? $nominal : 0;

            $masuk = $brankasHariIni->masuk + $nominalMasuk;
            $keluar = $brankasHariIni->keluar + $nominalKeluar;
            $total = $brankasHariIni->saldo_awal + $masuk - $keluar;

            $brankasHariIni->update([
                'masuk' => $masuk,
                'keluar' => $keluar,
                'total' => $total,
            ]);

            DB::commit();

            $slipUrl = in_array($jenis, ['setor', 'koreksi_tarik'], true)
                ? url('/teller/slip-setor/'.$trx->id)
                : url('/teller/slip-tarik/'.$trx->id);

            $jenisLabel = match ($jenis) {
                'setor' => 'Setoran Tunai',
                'tarik' => 'Penarikan Tunai',
                'koreksi_setor' => 'Koreksi Setoran (Batal Uang Palsu/Robek)',
                'koreksi_tarik' => 'Koreksi Penarikan',
                default => 'Transaksi'
            };

            return redirect('/teller/dashboard')
                ->with('success', "Transaksi {$jenisLabel} berhasil diproses dengan Kode Sandi [{$kode_sandi}]! Ref: #TRX-".str_pad($trx->id, 5, '0', STR_PAD_LEFT))
                ->with('slip_url', $slipUrl)
                ->with('slip_jenis', $jenisLabel);

        } catch (UniqueConstraintViolationException|QueryException $e) {
            DB::rollBack();

            // Tangani race condition jika request idempotency duplikat masuk bersamaan
            if ($request->filled('idempotency_key')) {
                $existing = Transaksi::where('idempotency_key', $request->idempotency_key)->first();
                if ($existing) {
                    $slipUrl = in_array($existing->jenis_transaksi, ['setor', 'koreksi_tarik'], true)
                        ? url('/teller/slip-setor/'.$existing->id)
                        : url('/teller/slip-tarik/'.$existing->id);

                    return redirect('/teller/dashboard')
                        ->with('success', 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotensi Aktif). Dana tidak dipotong ganda. Ref: #TRX-'.str_pad($existing->id, 5, '0', STR_PAD_LEFT))
                        ->with('slip_url', $slipUrl)
                        ->with('slip_jenis', $existing->jenis_transaksi);
                }
            }

            return back()->with('error', 'Sistem Gagal: Permintaan transaksi duplikat terdeteksi.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Sistem Gagal: '.$e->getMessage());
        }
    }

    public function riwayat(Request $request): View
    {
        $userId = Auth::id();
        $tab = $request->query('tab', 'semua');

        $query = Transaksi::with(['nasabah', 'petugas', 'petugasPembatal', 'originalTransaksi', 'reversalTransaksi'])
            ->where('petugas_id', $userId);

        // Filter tab khusus: semua vs riwayat void/koreksi
        if ($tab === 'void') {
            $query->where(function ($sub) {
                $sub->where('status', 'dibatalkan')
                    ->orWhereIn('jenis_transaksi', ['koreksi_setor', 'koreksi_tarik'])
                    ->orWhereNotNull('ref_transaksi_id');
            });
        }

        // 1. Filter Rentang Tanggal atau Tanggal Tunggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [
                $request->tanggal_mulai.' 00:00:00',
                $request->tanggal_akhir.' 23:59:59',
            ]);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        // 2. Filter Jenis Transaksi
        if ($request->filled('jenis_transaksi') && $request->jenis_transaksi !== 'semua') {
            $query->where('jenis_transaksi', $request->jenis_transaksi);
        }

        // 3. Filter Status Transaksi
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        // 4. Pencarian Berdasarkan Kata Kunci (ID Trx, No Rekening, Nama Siswa, NIS)
        if ($request->filled('cari')) {
            $cari = trim($request->cari);
            $cleanId = ltrim(preg_replace('/[^0-9]/', '', $cari), '0');

            $query->where(function ($sub) use ($cari, $cleanId) {
                if (! empty($cleanId)) {
                    $sub->where('id', $cleanId);
                }
                $sub->orWhereHas('nasabah', function ($n) use ($cari) {
                    $n->where('nama_siswa', 'LIKE', '%'.$cari.'%')
                        ->orWhere('nis', 'LIKE', '%'.$cari.'%')
                        ->orWhere('no_rekening', 'LIKE', '%'.$cari.'%');
                });
            });
        }

        $totalFiltered = (clone $query)->count();
        $totalNominal = (clone $query)->where('status', '!=', 'dibatalkan')->sum('nominal');

        $riwayat = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('teller.riwayat', compact('riwayat', 'totalFiltered', 'totalNominal', 'tab'));
    }

    public function scanQr(): View
    {
        return view('teller.scan');
    }

    public function profil(): View
    {
        $user = Auth::user();

        return view('teller.profil', compact('user'));
    }

    public function updateProfil(Request $request, ProfileImageService $imageService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nama_petugas' => 'required|string|max:150',
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'],
            'email' => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
        ], [
            'nama_petugas.required' => 'Nama lengkap wajib diisi.',
            'nama_petugas.max' => 'Nama lengkap maksimal 150 karakter.',
            'no_hp.regex' => 'Format nomor telepon tidak valid. Gunakan angka, contoh: 081234567890.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'foto.file' => 'File foto yang dipilih tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.mimetypes' => 'Format file ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $oldValues = [
            'nama_petugas' => $user->nama_petugas,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
        ];

        $user->nama_petugas = $validated['nama_petugas'];
        $user->no_hp = $validated['no_hp'] ?? null;
        $user->email = $validated['email'] ?? null;

        if ($request->hasFile('foto')) {
            $user->foto = $imageService->validateAndStore($request->file('foto'), $user->foto);
        }

        $user->save();
        AvalisSyncHelper::autoSync(true);

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_PROFIL',
            modul: 'Profil',
            objekId: $user->username,
            oldValues: $oldValues,
            newValues: [
                'nama_petugas' => $user->nama_petugas,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
            ]
        );

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }

    // FUNGSI VOID / PEMBATALAN TRANSAKSI RESMI
    public function voidTransaksi(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'pin_supervisor' => 'required',
            'alasan_batal' => 'required|min:5|max:255',
        ]);

        // Cek PIN Otorisasi Supervisor
        if ($request->pin_supervisor !== '123456') {
            return back()->with('error', 'Gagal Void! PIN Otorisasi Supervisor SALAH.');
        }

        DB::beginTransaction();
        try {
            /** @var Transaksi|null $trxLama */
            $trxLama = Transaksi::where('id', $id)->lockForUpdate()->first();
            if (! $trxLama || $trxLama->status === 'dibatalkan') {
                DB::rollBack();

                return back()->with('error', 'Transaksi tidak valid atau sudah dibatalkan sebelumnya.');
            }

            // Larang pembatalan transaksi koreksi
            if (in_array($trxLama->jenis_transaksi, ['koreksi_setor', 'koreksi_tarik'], true)) {
                DB::rollBack();

                return back()->with('error', 'Pembatalan Ditolak: Transaksi koreksi tidak dapat dibatalkan kembali.');
            }

            // Batasan SOP: Pembatalan hanya boleh dilakukan pada hari yang sama
            if (! $trxLama->created_at->isToday()) {
                DB::rollBack();

                return back()->with('error', 'Pembatalan Ditolak: Sesuai SOP perbankan, void transaksi hanya diizinkan pada hari yang sama sebelum kas harian ditutup.');
            }

            // Batasan SOP: Cek apakah Daily Report hari ini sudah disetujui / closed
            if ($trxLama->daily_report_id) {
                $dailyReport = DailyReport::find($trxLama->daily_report_id);
                if ($dailyReport && $dailyReport->status === 'approved') {
                    DB::rollBack();

                    return back()->with('error', 'Pembatalan Ditolak: Transaksi ini sudah masuk dalam Daily Report yang telah disetujui (Approved) oleh Supervisor.');
                }
            }

            /** @var Nasabah|null $nasabah */
            $nasabah = Nasabah::where('id', $trxLama->nasabah_id)->lockForUpdate()->first();
            if (! $nasabah) {
                DB::rollBack();

                return back()->with('error', 'Data nasabah tidak ditemukan.');
            }

            // Reversal Saldo Nasabah
            if ($trxLama->jenis_transaksi === 'setor') {
                if ($nasabah->saldo < $trxLama->nominal) {
                    DB::rollBack();

                    return back()->with('error', 'Gagal Void! Saldo nasabah saat ini (Rp '.number_format($nasabah->saldo, 0, ',', '.').') tidak mencukupi untuk membatalkan setoran sebesar Rp '.number_format($trxLama->nominal, 0, ',', '.').'.');
                }
                $saldo_baru = $nasabah->saldo - $trxLama->nominal;
            } else {
                $saldo_baru = $nasabah->saldo + $trxLama->nominal;
            }
            $nasabah->update(['saldo' => $saldo_baru]);

            // Buat Catatan Transaksi Koreksi Pembalik Sesuai SOP Kode Sandi (102 Koreksi Setor, 202 Koreksi Tarik)
            $jenisReversal = ($trxLama->jenis_transaksi === 'setor') ? 'koreksi_setor' : 'koreksi_tarik';
            $sandiReversal = ($trxLama->jenis_transaksi === 'setor') ? '102' : '202';

            $reversalTrx = Transaksi::create([
                'nasabah_id' => $nasabah->id,
                'petugas_id' => Auth::id(),
                'nama_petugas' => Auth::user()->nama_petugas ?? 'Teller',
                'jenis_layanan' => $trxLama->jenis_layanan ?? 'Tabungan',
                'jenis_transaksi' => $jenisReversal,
                'kode_sandi' => $sandiReversal,
                'nominal' => $trxLama->nominal,
                'status' => 'sukses',
                'ref_transaksi_id' => $trxLama->id,
                'alasan_batal' => $request->alasan_batal,
            ]);

            // Update status transaksi asli
            $trxLama->update([
                'status' => 'dibatalkan',
                'alasan_batal' => $request->alasan_batal,
                'dibatalkan_oleh' => Auth::id(),
                'dibatalkan_at' => now(),
                'ref_transaksi_id' => $reversalTrx->id,
            ]);

            // Buat Jurnal Balik Akuntansi
            $ket = "KOREKSI VOID [{$sandiReversal}]: Pembatalan transaksi #TRX-{$trxLama->id} {$trxLama->jenis_transaksi} ({$request->alasan_batal})";
            Jurnal::create([
                'transaksi_id' => $reversalTrx->id,
                'keterangan' => $ket,
                'debit' => ($trxLama->jenis_transaksi !== 'setor') ? $trxLama->nominal : 0,
                'kredit' => ($trxLama->jenis_transaksi === 'setor') ? $trxLama->nominal : 0,
                'tanggal' => now()->toDateString(),
            ]);

            // Reversal Brankas Kas Fisik
            $brankas = Brankas::getOrCreateToday(now()->toDateString(), true);
            if ($brankas) {
                $masuk = ($trxLama->jenis_transaksi === 'setor') ? $brankas->masuk - $trxLama->nominal : $brankas->masuk;
                $keluar = ($trxLama->jenis_transaksi === 'tarik') ? $brankas->keluar - $trxLama->nominal : $brankas->keluar;
                $total = $brankas->saldo_awal + $masuk - $keluar;
                $brankas->update([
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                    'total' => $total,
                ]);
            }

            // Catat ke Audit Log
            AuditHelper::log(
                jenisAktivitas: 'VOID_TRANSAKSI',
                modul: 'Teller',
                objekId: 'TRX-'.$trxLama->id,
                oldValues: ['status' => 'sukses', 'nominal' => $trxLama->nominal],
                newValues: [
                    'status' => 'dibatalkan',
                    'alasan_batal' => $request->alasan_batal,
                    'reversal_trx_id' => $reversalTrx->id,
                    'reversal_sandi' => $sandiReversal,
                ],
                user: Auth::user()
            );

            DB::commit();

            return back()->with('success', "Transaksi #TRX-{$id} BERHASIL DI-VOID! Transaksi koreksi [{$sandiReversal}] telah dibuat dan saldo nasabah telah disesuaikan.");

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal Void Sistem: '.$e->getMessage());
        }
    }
}
