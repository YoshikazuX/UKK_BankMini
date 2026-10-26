<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TellerController extends Controller
{
    public function index(Request $request)
    {
        $katakunci = $request->cari;

        $query = DB::table('nasabah');
        if ($katakunci) {
            $query->where('nis', 'LIKE', "%" . $katakunci . "%")
                  ->orWhere('nama_siswa', 'LIKE', "%" . $katakunci . "%");
        }

        $nasabah = $query->get();

        // ALGORITMA SORTING PINTAR (Tingkat -> Jurusan -> SubKelas -> NIS)
        $nasabah = $nasabah->sortBy(function ($item) {
            $k = strtoupper($item->kelas);

            // 1. Deteksi Tingkat (Grade)
            $grade = 99;
            if (preg_match('/\bXII\b/', $k)) $grade = 3;
            elseif (preg_match('/\bXI\b/', $k)) $grade = 2;
            elseif (preg_match('/\bX\b/', $k)) $grade = 1;

            // 2. Deteksi Jurusan (Sesuai Urutan Kakak)
            $jurusan = 99;
            if (preg_match('/\bRPL\b/', $k)) $jurusan = 1;
            elseif (preg_match('/\bTKJ\b/', $k)) $jurusan = 2;
            elseif (preg_match('/\bDKV\b/', $k)) $jurusan = 3;
            elseif (preg_match('/\bAK\b/', $k)) $jurusan = 4;
            elseif (preg_match('/\bLPS\b/', $k)) $jurusan = 5;
            elseif (preg_match('/\bMP\b/', $k)) $jurusan = 6;
            elseif (preg_match('/\bBR\b/', $k)) $jurusan = 7;
            elseif (preg_match('/\bBD\b/', $k)) $jurusan = 8;

            // 3. Deteksi Sub-kelas (1/2/3/4 atau I/II/III/IV)
            $sub = 99;
            if (preg_match('/\b(?:4|IV)\b/', $k)) $sub = 4;
            elseif (preg_match('/\b(?:3|III)\b/', $k)) $sub = 3;
            elseif (preg_match('/\b(?:2|II)\b/', $k)) $sub = 2;
            elseif (preg_match('/\b(?:1|I)\b/', $k)) $sub = 1;

            // Menggabungkan skor dan NIS agar urut sempurna!
            return sprintf('%02d-%02d-%02d-%s', $grade, $jurusan, $sub, $item->nis);
        })->values();

        $today = Carbon::today()->toDateString();
        $brankasHariIni = DB::table('brankas')->where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;
        $pendingPengajuan = DB::table('pengajuan_kas')->where('user_id', Auth::id())->where('status', 'pending')->count();

        return view('teller.dashboard', compact('nasabah', 'kasTersedia', 'kasAwal', 'pendingPengajuan'));
    }

    public function cariByNis($nis)
    {
        $nasabah = DB::table('nasabah')->where('nis', $nis)->first();
        if ($nasabah) {
            return redirect('/teller/transaksi/'.$nasabah->id);
        }
        return redirect('/teller/dashboard')->with('error', 'Nasabah dengan NIS '.$nis.' tidak ditemukan!');
    }

    public function formTransaksi($id)
    {
        $nasabah = DB::table('nasabah')->where('id', $id)->first();
        if (!$nasabah) {
            return redirect('/teller/dashboard')->with('error', 'Data nasabah tidak ditemukan!');
        }

        $today = Carbon::today()->toDateString();
        $brankasHariIni = DB::table('brankas')->where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        return view('teller.transaksi', compact('nasabah', 'kasTersedia', 'kasAwal'));
    }

    public function prosesTransaksi(Request $request)
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
            $existing = DB::table('transaksi')->where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return redirect('/teller/dashboard')->with('success', 'Transaksi telah berhasil diproses sebelumnya (Proteksi Idempotency Aktif). Dana tidak dipotong ganda.');
            }
        }

        $nasabah_id = $request->nasabah_id;
        $jenis = $request->jenis_transaksi;
        $nominal = $request->nominal;
        $petugas_id = Auth::id();

        // 2. Kode Sandi Transaksi Standar Perbankan
        $kode_sandi = match($jenis) {
            'setor' => '101',
            'tarik' => '201',
            'koreksi_setor' => '102',
            'koreksi_tarik' => '202',
            'admin' => '301',
            default => '100'
        };

        DB::beginTransaction();
        try {
            $nasabah = DB::table('nasabah')->where('id', $nasabah_id)->lockForUpdate()->first();
            if (!$nasabah) {
                return back()->with('error', 'Data nasabah tidak ditemukan.');
            }

            $minSaldoMengendap = \App\Helpers\SettingHelper::getSaldoMengendapMin();

            // Aturan Saldo Mengendap Minimal Perbankan Mini untuk Penarikan
            if ($jenis == 'tarik' && ($nasabah->saldo - $nominal) < $minSaldoMengendap) {
                $formattedMin = number_format($minSaldoMengendap, 0, ',', '.');
                return back()->with('error', "Gagal! Penarikan ditolak karena melanggar aturan saldo mengendap minimal Rp {$formattedMin}.");
            }

            // Otorisasi Form Verifikasi PIN Keamanan Nasabah (Wajib Sesuai Standar SOP Penarikan)
            if ($jenis == 'tarik') {
                $pinNasabah = $request->pin_nasabah ?? $request->pin_keamanan;
                if (empty($pinNasabah)) {
                    return back()->with('error', 'Penarikan Ditolak: Otorisasi PIN Keamanan Nasabah wajib diisi untuk verifikasi penarikan tunai!');
                }

                $pinValid = false;
                if (!empty($nasabah->pin_keamanan) && \Illuminate\Support\Facades\Hash::check($pinNasabah, $nasabah->pin_keamanan)) {
                    $pinValid = true;
                } elseif ($pinNasabah === '123456') { // PIN Default Nasabah
                    $pinValid = true;
                }

                if (!$pinValid) {
                    return back()->with('error', 'Penarikan Ditolak: PIN Keamanan Nasabah SALAH! Pastikan nasabah menginput 6 digit PIN kartu yang benar.');
                }
            }

            // Aturan Saldo Cukup untuk Koreksi Setoran (Pembatalan Uang Palsu/Robek)
            if ($jenis == 'koreksi_setor' && $nasabah->saldo < $nominal) {
                return back()->with('error', 'Gagal! Saldo nasabah tidak mencukupi untuk dilakukan koreksi setoran.');
            }

            // Aturan Potongan Admin
            if ($jenis == 'admin' && $nasabah->saldo < $nominal) {
                return back()->with('error', 'Gagal! Saldo nasabah tidak cukup untuk dipotong biaya administrasi.');
            }

            // 3. PROTEKSI KAS MINUS PERBANKAN: JANGAN SAMPAI KAS FISIK DI LOKET/BRANKAS MINUS!
            // Cek ketersediaan uang tunai riil di laci kas loket hari ini
            $brankasHariIni = DB::table('brankas')->where('tanggal', now()->toDateString())->lockForUpdate()->first();
            $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;

            if (in_array($jenis, ['tarik', 'koreksi_setor']) && $nominal > $kasTersedia) {
                DB::rollback();
                $formattedKas = number_format($kasTersedia, 0, ',', '.');
                $formattedNom = number_format($nominal, 0, ',', '.');
                return back()
                    ->with('error', "PENARIKAN DITOLAK: Uang kas fisik di loket tidak mencukupi! Sisa kas tersedia saat ini hanya Rp {$formattedKas} (Dibutuhkan: Rp {$formattedNom}). Kas dilarang minus! Silakan buat Pengajuan Kas ke Supervisor.")
                    ->with('kas_habis', true)
                    ->with('kas_tersedia', $kasTersedia);
            }

            $transaksi_id = DB::table('transaksi')->insertGetId([
                'nasabah_id' => $nasabah_id,
                'petugas_id' => $petugas_id,
                'jenis_transaksi' => $jenis,
                'kode_sandi' => $kode_sandi,
                'nominal' => $nominal,
                'status' => 'pending',
                'idempotency_key' => $request->idempotency_key,
                'alasan_batal' => $request->alasan_koreksi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Hitung Saldo Baru
            if ($jenis == 'setor' || $jenis == 'koreksi_tarik') {
                $saldo_baru = $nasabah->saldo + $nominal;
            } else {
                $saldo_baru = $nasabah->saldo - $nominal;
            }
            DB::table('nasabah')->where('id', $nasabah_id)->update(['saldo' => $saldo_baru]);

            // Pembukuan Jurnal Akuntansi
            $ket = match($jenis) {
                'setor' => 'Setoran Tunai [101] - ' . $nasabah->nama_siswa,
                'tarik' => 'Penarikan Tunai [201] - ' . $nasabah->nama_siswa,
                'koreksi_setor' => 'KOREKSI SETOR [102] (' . ($request->alasan_koreksi ?? 'Uang palsu/robek') . ') - ' . $nasabah->nama_siswa,
                'koreksi_tarik' => 'KOREKSI TARIK [202] (' . ($request->alasan_koreksi ?? 'Pembatalan salah tarik') . ') - ' . $nasabah->nama_siswa,
                'admin' => 'Biaya Administrasi Buku [301] - ' . $nasabah->nama_siswa,
            };

            $isDebit = in_array($jenis, ['setor', 'koreksi_tarik']);
            DB::table('jurnal')->insert([
                'transaksi_id' => $transaksi_id,
                'keterangan' => $ket,
                'debit' => $isDebit ? $nominal : 0,
                'kredit' => !$isDebit ? $nominal : 0,
                'tanggal' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mutasi Kas Brankas Fisik
            $brankasHariIni = DB::table('brankas')->where('tanggal', now()->toDateString())->first();
            $nominalMasuk = in_array($jenis, ['setor', 'koreksi_tarik']) ? $nominal : 0;
            $nominalKeluar = in_array($jenis, ['tarik', 'admin', 'koreksi_setor']) ? $nominal : 0;

            if ($brankasHariIni) {
                $masuk = $brankasHariIni->masuk + $nominalMasuk;
                $keluar = $brankasHariIni->keluar + $nominalKeluar;
                $total = $brankasHariIni->saldo_awal + $masuk - $keluar;

                DB::table('brankas')->where('id', $brankasHariIni->id)->update([
                    'masuk' => $masuk, 'keluar' => $keluar, 'total' => $total, 'updated_at' => now(),
                ]);
            } else {
                $kemarin = DB::table('brankas')->orderBy('tanggal', 'desc')->first();
                $saldo_awal = $kemarin ? $kemarin->total : 0;
                $total = $saldo_awal + $nominalMasuk - $nominalKeluar;

                DB::table('brankas')->insert([
                    'tanggal' => now()->toDateString(),
                    'saldo_awal' => $saldo_awal,
                    'masuk' => $nominalMasuk,
                    'keluar' => $nominalKeluar,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $slipUrl = in_array($jenis, ['setor', 'koreksi_tarik'])
                ? url('/teller/slip-setor/' . $transaksi_id)
                : url('/teller/slip-tarik/' . $transaksi_id);

            $jenisLabel = match($jenis) {
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

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Sistem Gagal: ' . $e->getMessage());
        }
    }

    public function riwayat()
    {
        $riwayat = DB::table('transaksi')
                    ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                    ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis')
                    ->where('transaksi.petugas_id', Auth::id())
                    ->orderBy('transaksi.created_at', 'desc')
                    ->get();

        return view('teller.riwayat', compact('riwayat'));
    }

    public function scanQr()
    {
        return view('teller.scan');
    }

    public function profil()
    {
        $user = Auth::user();
        return view('teller.profil', compact('user'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $dataUpdate = ['no_hp' => $request->no_hp, 'updated_at' => now()];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public', $filename);
            $dataUpdate['foto'] = $filename;
        }

        DB::table('users')->where('id', $user->id)->update($dataUpdate);
        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    // FUNGSI VOID / PEMBATALAN TRANSAKSI
    public function voidTransaksi(Request $request, $id)
    {
        $request->validate([
            'pin_supervisor' => 'required',
            'alasan_batal' => 'required|min:5'
        ]);

        // Cek PIN Otorisasi Supervisor
        if ($request->pin_supervisor !== '123456') {
            return back()->with('error', 'Gagal Void! PIN Otorisasi Supervisor SALAH.');
        }

        DB::beginTransaction();
        try {
            $trxLama = DB::table('transaksi')->where('id', $id)->lockForUpdate()->first();
            if (!$trxLama || $trxLama->status == 'dibatalkan') {
                return back()->with('error', 'Transaksi tidak valid atau sudah dibatalkan.');
            }

            $nasabah = DB::table('nasabah')->where('id', $trxLama->nasabah_id)->lockForUpdate()->first();

            // Reversal Saldo Nasabah
            if ($trxLama->jenis_transaksi == 'setor') {
                $saldo_baru = $nasabah->saldo - $trxLama->nominal;
            } else {
                $saldo_baru = $nasabah->saldo + $trxLama->nominal;
            }
            DB::table('nasabah')->where('id', $nasabah->id)->update(['saldo' => $saldo_baru]);

            // Update status transaksi
            DB::table('transaksi')->where('id', $id)->update([
                'status' => 'dibatalkan',
                'alasan_batal' => $request->alasan_batal,
                'updated_at' => now()
            ]);

            // Buat Jurnal Balik
            $ket = 'KOREKSI VOID: Pembatalan transaksi ' . $trxLama->jenis_transaksi . ' (' . $request->alasan_batal . ')';
            DB::table('jurnal')->insert([
                'transaksi_id' => $trxLama->id, 'keterangan' => $ket,
                'debit' => ($trxLama->jenis_transaksi != 'setor') ? $trxLama->nominal : 0,
                'kredit' => ($trxLama->jenis_transaksi == 'setor') ? $trxLama->nominal : 0,
                'tanggal' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now(),
            ]);

            // Reversal Brankas
            $brankas = DB::table('brankas')->where('tanggal', now()->toDateString())->first();
            if ($brankas) {
                $masuk = ($trxLama->jenis_transaksi == 'setor') ? $brankas->masuk - $trxLama->nominal : $brankas->masuk;
                $keluar = ($trxLama->jenis_transaksi == 'tarik') ? $brankas->keluar - $trxLama->nominal : $brankas->keluar;
                $total = $brankas->saldo_awal + $masuk - $keluar;
                DB::table('brankas')->where('id', $brankas->id)->update([
                    'masuk' => $masuk, 'keluar' => $keluar, 'total' => $total, 'updated_at' => now()
                ]);
            }

            DB::commit();
            return back()->with('success', 'Transaksi Berhasil Di-VOID! Saldo Nasabah telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal Void Sistem: ' . $e->getMessage());
        }
    }
}
