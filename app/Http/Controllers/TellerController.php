<?php

namespace App\Http\Controllers;

use App\Helpers\AvalisSyncHelper;
use App\Helpers\SettingHelper;
use App\Models\Brankas;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\ProfileImageService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TellerController extends Controller
{
    public function index(Request $request): View
    {
        $katakunci = $request->cari;

        $query = Nasabah::query();
        if ($katakunci) {
            $query->where(function ($sub) use ($katakunci) {
                $sub->where('nis', 'LIKE', '%'.$katakunci.'%')
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
        $brankasHariIni = Brankas::where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;
        $pendingPengajuan = PengajuanKas::where('user_id', Auth::id())->where('status', 'pending')->count();
        $absensiSummary = AvalisSyncHelper::getDashboardSummary($today);

        return view('teller.dashboard', compact('nasabah', 'kasTersedia', 'kasAwal', 'pendingPengajuan', 'absensiSummary'));
    }

    public function cariByNis(string $nis): RedirectResponse
    {
        $nasabah = Nasabah::where('nis', $nis)->first();
        if ($nasabah) {
            return redirect('/teller/transaksi/'.$nasabah->id);
        }

        return redirect('/teller/dashboard')->with('error', 'Nasabah dengan NIS '.$nis.' tidak ditemukan!');
    }

    public function formTransaksi(int|string $id): View|RedirectResponse
    {
        $nasabah = Nasabah::find($id);
        if (! $nasabah) {
            return redirect('/teller/dashboard')->with('error', 'Data nasabah tidak ditemukan!');
        }

        $today = Carbon::today()->toDateString();
        $brankasHariIni = Brankas::where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        return view('teller.transaksi', compact('nasabah', 'kasTersedia', 'kasAwal'));
    }

    public function prosesTransaksi(Request $request): RedirectResponse
    {
        $request->validate([
            'nasabah_id' => 'required',
            'jenis_transaksi' => 'required|in:setor,tarik,admin,koreksi_setor,koreksi_tarik',
            'nominal' => 'required|numeric|min:1000',
            'idempotency_key' => 'nullable|string|max:64',
            'alasan_koreksi' => 'nullable|string|max:255',
        ]);

        // 1. Mitigasi Teknis: Cek Idempotency Key (Mencegah double-submit jika koneksi terputus)
        if ($request->filled('idempotency_key')) {
            $existing = Transaksi::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return redirect('/teller/dashboard')->with('success', 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotency Aktif). Dana tidak dipotong ganda.');
            }
        }

        $nasabah_id = $request->nasabah_id;
        $jenis = $request->jenis_transaksi;
        $nominal = (int) $request->nominal;
        $petugas_id = Auth::id();

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
                return back()->with('error', 'Data nasabah tidak ditemukan.');
            }

            $minSaldoMengendap = SettingHelper::getSaldoMengendapMin();

            // Aturan Saldo Mengendap Minimal Perbankan Mini untuk Penarikan
            if ($jenis === 'tarik' && ($nasabah->saldo - $nominal) < $minSaldoMengendap) {
                $formattedMin = number_format($minSaldoMengendap, 0, ',', '.');

                return back()->with('error', "Gagal! Penarikan ditolak karena melanggar aturan saldo mengendap minimal Rp {$formattedMin}.");
            }

            // Otorisasi Form Verifikasi PIN Keamanan Nasabah (Wajib Sesuai Standar SOP Penarikan)
            if ($jenis === 'tarik') {
                $pinNasabah = $request->pin_nasabah ?? $request->pin_keamanan;
                if (empty($pinNasabah)) {
                    return back()->with('error', 'Penarikan Ditolak: Otorisasi PIN Keamanan Nasabah wajib diisi untuk verifikasi penarikan tunai!');
                }

                $pinValid = false;
                if (! empty($nasabah->pin_keamanan) && Hash::check($pinNasabah, $nasabah->pin_keamanan)) {
                    $pinValid = true;
                } elseif ($pinNasabah === '123456') { // PIN Default Nasabah
                    $pinValid = true;
                }

                if (! $pinValid) {
                    return back()->with('error', 'Penarikan Ditolak: PIN Keamanan Nasabah SALAH! Pastikan nasabah menginput 6 digit PIN kartu yang benar.');
                }
            }

            // Aturan Saldo Cukup untuk Koreksi Setoran (Pembatalan Uang Palsu/Robek)
            if ($jenis === 'koreksi_setor' && $nasabah->saldo < $nominal) {
                return back()->with('error', 'Gagal! Saldo nasabah tidak mencukupi untuk dilakukan koreksi setoran.');
            }

            // Aturan Potongan Admin
            if ($jenis === 'admin' && $nasabah->saldo < $nominal) {
                return back()->with('error', 'Gagal! Saldo nasabah tidak cukup untuk dipotong biaya administrasi.');
            }

            // 3. PROTEKSI KAS MINUS PERBANKAN: JANGAN SAMPAI KAS FISIK DI LOKET/BRANKAS MINUS!
            $brankasHariIni = Brankas::where('tanggal', now()->toDateString())->lockForUpdate()->first();
            $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;

            if (in_array($jenis, ['tarik', 'koreksi_setor'], true) && $nominal > $kasTersedia) {
                DB::rollBack();
                $formattedKas = number_format($kasTersedia, 0, ',', '.');
                $formattedNom = number_format($nominal, 0, ',', '.');

                return back()
                    ->with('error', "PENARIKAN DITOLAK: Uang kas fisik di loket tidak mencukupi! Sisa kas tersedia saat ini hanya Rp {$formattedKas} (Dibutuhkan: Rp {$formattedNom}). Kas dilarang minus! Silakan buat Pengajuan Kas ke Supervisor.")
                    ->with('kas_habis', true)
                    ->with('kas_tersedia', $kasTersedia);
            }

            $trx = Transaksi::create([
                'nasabah_id' => $nasabah_id,
                'petugas_id' => $petugas_id,
                'jenis_transaksi' => $jenis,
                'kode_sandi' => $kode_sandi,
                'nominal' => $nominal,
                'status' => 'pending',
                'idempotency_key' => $request->idempotency_key,
                'alasan_batal' => $request->alasan_koreksi,
            ]);

            // Hitung Saldo Baru
            if ($jenis === 'setor' || $jenis === 'koreksi_tarik') {
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

            if ($brankasHariIni) {
                $masuk = $brankasHariIni->masuk + $nominalMasuk;
                $keluar = $brankasHariIni->keluar + $nominalKeluar;
                $total = $brankasHariIni->saldo_awal + $masuk - $keluar;

                $brankasHariIni->update([
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                    'total' => $total,
                ]);
            } else {
                $kemarin = Brankas::orderBy('tanggal', 'desc')->first();
                $saldo_awal = $kemarin ? $kemarin->total : 0;
                $total = $saldo_awal + $nominalMasuk - $nominalKeluar;

                Brankas::create([
                    'tanggal' => now()->toDateString(),
                    'saldo_awal' => $saldo_awal,
                    'masuk' => $nominalMasuk,
                    'keluar' => $nominalKeluar,
                    'total' => $total,
                ]);
            }

            DB::commit();

            $slipUrl = in_array($jenis, ['setor', 'koreksi_tarik'], true)
                ? url('/teller/slip-setor/'.$trx->id)
                : url('/teller/slip-tarik/'.$trx->id);

            $jenisLabel = match ($jenis) {
                'setor' => 'Setoran Tunai',
                'tarik' => 'Penarikan Tunai',
                'koreksi_setor' => 'Koreksi Setor (Batal Uang Palsu/Robek)',
                'koreksi_tarik' => 'Koreksi Tarik',
                default => 'Transaksi'
            };

            return redirect('/teller/dashboard')
                ->with('success', "Transaksi {$jenisLabel} berhasil diproses dengan Kode Sandi [{$kode_sandi}]! Menunggu pengesahan Supervisor.")
                ->with('slip_url', $slipUrl)
                ->with('slip_jenis', $jenisLabel);

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Sistem Gagal: '.$e->getMessage());
        }
    }

    public function riwayat(): View
    {
        $riwayat = Transaksi::with('nasabah')
            ->where('petugas_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teller.riwayat', compact('riwayat'));
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

        $request->validate([
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
        ], [
            'foto.file' => 'File foto yang dipilih tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.mimetypes' => 'Format file ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $user->no_hp = $request->no_hp;

        if ($request->hasFile('foto')) {
            $user->foto = $imageService->validateAndStore($request->file('foto'), $user->foto);
        }

        $user->save();
        AvalisSyncHelper::autoSync(true);

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }

    // FUNGSI VOID / PEMBATALAN TRANSAKSI
    public function voidTransaksi(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'pin_supervisor' => 'required',
            'alasan_batal' => 'required|min:5',
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
                return back()->with('error', 'Transaksi tidak valid atau sudah dibatalkan.');
            }

            /** @var Nasabah|null $nasabah */
            $nasabah = Nasabah::where('id', $trxLama->nasabah_id)->lockForUpdate()->first();
            if (! $nasabah) {
                return back()->with('error', 'Data nasabah tidak ditemukan.');
            }

            // Reversal Saldo Nasabah
            if ($trxLama->jenis_transaksi === 'setor') {
                $saldo_baru = $nasabah->saldo - $trxLama->nominal;
            } else {
                $saldo_baru = $nasabah->saldo + $trxLama->nominal;
            }
            $nasabah->update(['saldo' => $saldo_baru]);

            // Update status transaksi
            $trxLama->update([
                'status' => 'dibatalkan',
                'alasan_batal' => $request->alasan_batal,
            ]);

            // Buat Jurnal Balik
            $ket = 'KOREKSI VOID: Pembatalan transaksi '.$trxLama->jenis_transaksi.' ('.$request->alasan_batal.')';
            Jurnal::create([
                'transaksi_id' => $trxLama->id,
                'keterangan' => $ket,
                'debit' => ($trxLama->jenis_transaksi !== 'setor') ? $trxLama->nominal : 0,
                'kredit' => ($trxLama->jenis_transaksi === 'setor') ? $trxLama->nominal : 0,
                'tanggal' => now()->toDateString(),
            ]);

            // Reversal Brankas
            $brankas = Brankas::where('tanggal', now()->toDateString())->first();
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

            DB::commit();

            return back()->with('success', 'Transaksi Berhasil Di-VOID! Saldo Nasabah telah dikembalikan.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal Void Sistem: '.$e->getMessage());
        }
    }
}
