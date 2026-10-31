<?php

namespace App\Http\Controllers;

use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\PengajuanKas;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengajuanKasController extends Controller
{
    /**
     * Tampilan Pengajuan Kas untuk Teller (Formulir & Riwayat Sendiri)
     */
    public function indexTeller(Request $request): View
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        // Cek kondisi kas fisik saat ini di brankas loket
        $brankasHariIni = Brankas::where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        // Riwayat pengajuan kas milik teller yang login
        $pengajuanList = PengajuanKas::with('approver')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $pengajuanList->each(function ($p) {
            $p->nama_spv = $p->approver->nama_petugas ?? '-';
        });

        return view('teller.pengajuan-kas', compact('kasTersedia', 'kasAwal', 'pengajuanList'));
    }

    /**
     * Teller Mengirim Pengajuan Kas Baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'alasan' => 'required|string|max:500',
        ]);

        $today = Carbon::today()->toDateString();
        $countToday = PengajuanKas::whereDate('tanggal', $today)->count() + 1;
        $nomorPengajuan = 'PK-'.date('Ymd').'-'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);

        PengajuanKas::create([
            'nomor_pengajuan' => $nomorPengajuan,
            'user_id' => Auth::id(),
            'tanggal' => $today,
            'nominal' => (int) $validated['nominal'],
            'alasan' => $validated['alasan'],
            'status' => 'pending',
        ]);

        return back()->with('success', "Pengajuan kas #{$nomorPengajuan} sebesar Rp ".number_format($validated['nominal'], 0, ',', '.').' berhasil dikirim! Menunggu persetujuan Supervisor / Accounting.');
    }

    /**
     * Tampilan Pengajuan Kas untuk Supervisor & Accounting
     */
    public function indexSupervisor(Request $request): View
    {
        $today = Carbon::today()->toDateString();

        // Info Kas Saat Ini
        $brankasHariIni = Brankas::where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        $query = PengajuanKas::with(['user', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $pengajuanList = $query->orderBy('created_at', 'desc')->get();

        $pengajuanList->each(function ($p) {
            $p->nama_teller = $p->user->nama_petugas ?? '-';
            $p->nama_spv = $p->approver->nama_petugas ?? '-';
        });

        $role = strtolower(Auth::user()->role ?? 'supervisor');

        return view('supervisor.pengajuan-kas', compact('kasTersedia', 'kasAwal', 'pengajuanList', 'role'));
    }

    /**
     * Supervisor / Accounting Menyetujui Pengajuan Kas (Kas Otomatis Bertambah)
     */
    public function setujui(Request $request, int|string $id): RedirectResponse
    {
        $pengajuan = PengajuanKas::find($id);
        if (! $pengajuan) {
            return back()->with('error', 'Data pengajuan kas tidak ditemukan!');
        }

        if ($pengajuan->status !== 'pending') {
            return back()->with('error', 'Pengajuan kas ini sudah pernah diproses sebelumnya.');
        }

        $today = Carbon::today()->toDateString();

        DB::beginTransaction();
        try {
            // 1. Update status pengajuan kas
            $pengajuan->update([
                'status' => 'disetujui',
                'disetujui_oleh' => Auth::id(),
                'catatan_spv' => $request->catatan_spv ?? 'Disetujui untuk pemenuhan likuiditas loket',
            ]);

            // 2. Tambahkan dana masuk ke kas brankas hari ini
            $brankasHariIni = Brankas::getOrCreateToday($today, true);
            $masuk = $brankasHariIni->masuk + $pengajuan->nominal;
            $total = $brankasHariIni->saldo_awal + $masuk - $brankasHariIni->keluar;

            $brankasHariIni->update([
                'masuk' => $masuk,
                'total' => $total,
            ]);

            // 3. Catat di Jurnal Akuntansi sebagai Droping Dana Kas Operasional
            $teller = User::find($pengajuan->user_id);
            $namaTeller = $teller ? $teller->nama_petugas : 'Teller';

            Jurnal::create([
                'transaksi_id' => null, // null = Transaksi Non-Nasabah / Droping Kas Internal
                'keterangan' => "PENERIMAAN DROPING KAS OPERASIONAL [{$pengajuan->nomor_pengajuan}] - {$namaTeller}",
                'debit' => $pengajuan->nominal,
                'kredit' => 0,
                'tanggal' => $today,
            ]);

            DB::commit();

            return back()->with('success', "Pengajuan kas #{$pengajuan->nomor_pengajuan} sebesar Rp ".number_format($pengajuan->nominal, 0, ',', '.').' BERHASIL DISETUJUI! Kas operasional loket otomatis bertambah.');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses persetujuan pengajuan kas: '.$e->getMessage());
        }
    }

    /**
     * Supervisor / Accounting Menolak Pengajuan Kas
     */
    public function tolak(Request $request, int|string $id): RedirectResponse
    {
        $pengajuan = PengajuanKas::find($id);
        if (! $pengajuan) {
            return back()->with('error', 'Data pengajuan kas tidak ditemukan!');
        }

        $pengajuan->update([
            'status' => 'ditolak',
            'disetujui_oleh' => Auth::id(),
            'catatan_spv' => $request->catatan_spv ?? 'Ditolak: Kas loket dinilai masih mencukupi atau anggaran belum tersedia.',
        ]);

        return back()->with('success', "Pengajuan kas #{$pengajuan->nomor_pengajuan} telah DITOLAK.");
    }

    /**
     * Fitur Atur / Input Kas Awal Operasional Hari Ini
     */
    public function setKasAwal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'saldo_awal' => 'required|numeric|min:0',
        ]);

        $today = Carbon::today()->toDateString();
        $saldoAwal = (int) $validated['saldo_awal'];

        DB::beginTransaction();
        try {
            $brankasHariIni = Brankas::where('tanggal', $today)->lockForUpdate()->first();

            if ($brankasHariIni) {
                $total = $saldoAwal + $brankasHariIni->masuk - $brankasHariIni->keluar;
                $brankasHariIni->update([
                    'saldo_awal' => $saldoAwal,
                    'total' => $total,
                ]);
            } else {
                Brankas::create([
                    'tanggal' => $today,
                    'saldo_awal' => $saldoAwal,
                    'masuk' => 0,
                    'keluar' => 0,
                    'total' => $saldoAwal,
                ]);
            }

            // Update opening_balance di daily_reports jika teller sudah buat draft
            DailyReport::whereDate('report_date', $today)
                ->where('status', '!=', 'approved')
                ->update(['opening_balance' => $saldoAwal]);

            DB::commit();

            return back()->with('success', 'Kas Awal operasional hari ini berhasil ditetapkan sebesar Rp '.number_format($saldoAwal, 0, ',', '.').'!');

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menetapkan Kas Awal: '.$e->getMessage());
        }
    }
}
