<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\NotificationHelper;
use App\Models\ApprovalHistory;
use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
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
        $brankasHariIni = Brankas::whereDate('tanggal', $today)->first();
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
        ], [
            'nominal.required' => 'Nominal pengajuan kas wajib diisi.',
            'nominal.min' => 'Nominal pengajuan kas minimal Rp 10.000.',
            'alasan.required' => 'Alasan permohonan penambahan kas wajib diisi.',
        ]);

        $today = Carbon::today()->toDateString();
        $countToday = PengajuanKas::whereDate('tanggal', $today)->count() + 1;
        $nomorPengajuan = 'PK-'.date('Ymd').'-'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanKas::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'user_id' => Auth::id(),
                'tanggal' => $today,
                'nominal' => (int) $validated['nominal'],
                'alasan' => $validated['alasan'],
                'status' => 'pending',
            ]);

            // Catat log audit aktivitas
            AuditHelper::log(
                jenisAktivitas: 'AJUKAN_DROPING_KAS',
                modul: 'Operasional Kas',
                objekId: $nomorPengajuan,
                oldValues: null,
                newValues: [
                    'nomor_pengajuan' => $nomorPengajuan,
                    'nominal' => (int) $validated['nominal'],
                    'alasan' => $validated['alasan'],
                ]
            );

            // Kirim notifikasi pekerjaan ke seluruh Supervisor & Accounting
            $namaTeller = Auth::user()->nama_petugas ?? 'Teller Loket';
            NotificationHelper::notifySupervisors([
                'task_type' => 'pengajuan_kas',
                'task_id' => $pengajuan->id,
                'nomor_referensi' => $nomorPengajuan,
                'nama_teller' => $namaTeller,
                'title' => 'Pengajuan Droping Kas Baru',
                'message' => "Teller {$namaTeller} mengajukan tambahan kas sebesar Rp ".number_format($pengajuan->nominal, 0, ',', '.')." ({$nomorPengajuan}).",
                'nominal' => $pengajuan->nominal,
                'status' => 'pending',
                'action_url' => url('/supervisor/pengajuan-kas/'.$pengajuan->id),
            ]);

            DB::commit();

            return back()->with('success', "Pengajuan kas #{$nomorPengajuan} sebesar Rp ".number_format($validated['nominal'], 0, ',', '.').' berhasil dikirim! Menunggu persetujuan Supervisor / Accounting.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengirim pengajuan kas: '.$e->getMessage());
        }
    }

    /**
     * Tampilan Pengajuan Kas untuk Supervisor & Accounting
     */
    public function indexSupervisor(Request $request): View
    {
        $today = Carbon::today()->toDateString();

        // Info Kas Saat Ini
        $brankasHariIni = Brankas::whereDate('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        $query = PengajuanKas::with(['user', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('tanggal', [$request->dari_tanggal, $request->sampai_tanggal]);
        } elseif ($request->filled('dari_tanggal')) {
            $query->whereDate('tanggal', '>=', $request->dari_tanggal);
        } elseif ($request->filled('sampai_tanggal')) {
            $query->whereDate('tanggal', '<=', $request->sampai_tanggal);
        }

        if ($request->filled('teller_id')) {
            $query->where('user_id', $request->teller_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('nama_petugas', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $pengajuanList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $pengajuanList->each(function ($p) {
            $p->nama_teller = $p->user->nama_petugas ?? '-';
            $p->nama_spv = $p->approver->nama_petugas ?? '-';
        });

        $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();
        $role = strtolower(Auth::user()->role ?? 'supervisor');

        return view('supervisor.pengajuan-kas', compact('kasTersedia', 'kasAwal', 'pengajuanList', 'role', 'tellers'));
    }

    /**
     * Detail Pengajuan Droping Kas untuk Pemeriksaan Mendalam Supervisor
     */
    public function show(int|string $id): View
    {
        $pengajuan = PengajuanKas::with(['user', 'approver', 'histories.supervisor'])->findOrFail($id);

        $teller = $pengajuan->user;
        $tgl = $pengajuan->tanggal ? $pengajuan->tanggal->toDateString() : Carbon::today()->toDateString();

        // 1. Kondisi Kas Loket & Brankas pada Tanggal Pengajuan
        $brankasTanggal = Brankas::whereDate('tanggal', $tgl)->first();
        $kasAwalLoket = $brankasTanggal ? (int) $brankasTanggal->saldo_awal : 0;
        $kasSisaLoket = $brankasTanggal ? (int) $brankasTanggal->total : 0;

        // 2. Aktivitas Transaksi Teller pada Tanggal Tersebut
        $trxTellerHariIni = Transaksi::where('petugas_id', $pengajuan->user_id)
            ->whereDate('created_at', $tgl)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalSetoranMasuk = (int) $trxTellerHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $jumlahSetoran = $trxTellerHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->count();

        $totalPenarikanKeluar = (int) $trxTellerHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');
        $jumlahPenarikan = $trxTellerHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->count();

        // Droping kas yang sudah disetujui sebelumnya untuk teller ini pada tanggal tersebut
        $dropingDisetujuiHariIni = (int) PengajuanKas::where('user_id', $pengajuan->user_id)
            ->whereDate('tanggal', $tgl)
            ->where('status', 'disetujui')
            ->where('id', '!=', $pengajuan->id)
            ->sum('nominal');

        // Posisi perkiraan kas laci teller saat ini
        $estimasiKasLaciTeller = $kasAwalLoket + $totalSetoranMasuk + $dropingDisetujuiHariIni - $totalPenarikanKeluar;

        // 3. Riwayat Pengajuan Kas Sebelumnya oleh Teller Ini
        $riwayatSebelumnya = PengajuanKas::with('approver')
            ->where('user_id', $pengajuan->user_id)
            ->where('id', '!=', $pengajuan->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Pengajuan lain yang masih pending dari teller ini
        $pengajuanPendingLain = PengajuanKas::where('user_id', $pengajuan->user_id)
            ->where('status', 'pending')
            ->where('id', '!=', $pengajuan->id)
            ->get();

        // Tandai notifikasi pekerjaan sebagai terbaca oleh supervisor yang sedang memeriksa
        NotificationHelper::markTaskAsRead(Auth::user(), 'pengajuan_kas', $pengajuan->id);

        return view('supervisor.detail-pengajuan-kas', compact(
            'pengajuan',
            'teller',
            'kasAwalLoket',
            'kasSisaLoket',
            'totalSetoranMasuk',
            'jumlahSetoran',
            'totalPenarikanKeluar',
            'jumlahPenarikan',
            'dropingDisetujuiHariIni',
            'estimasiKasLaciTeller',
            'riwayatSebelumnya',
            'pengajuanPendingLain'
        ));
    }

    /**
     * Supervisor / Accounting Menyetujui Pengajuan Kas (Kas Otomatis Bertambah)
     * Dilengkapi Proteksi Row Locking & Idempotensi Mencegah Double Approval
     */
    public function setujui(Request $request, int|string $id): RedirectResponse
    {
        $today = Carbon::today()->toDateString();
        $catatan = $request->catatan_spv ?? $request->catatan ?? 'Disetujui untuk pemenuhan likuiditas loket teller';

        DB::beginTransaction();
        try {
            // Row-level lock untuk mencegah balapan multi-supervisor (race condition)
            $pengajuan = PengajuanKas::where('id', $id)->lockForUpdate()->first();

            if (! $pengajuan) {
                DB::rollBack();

                return back()->with('error', 'Data pengajuan kas tidak ditemukan!');
            }

            if ($pengajuan->status !== 'pending') {
                DB::rollBack();
                $spvName = $pengajuan->approver?->nama_petugas ?? 'Supervisor lain';

                return back()->with('error', "Pencegahan Dobel Persetujuan: Pengajuan kas #{$pengajuan->nomor_pengajuan} sudah diproses sebelumnya dengan status [{$pengajuan->status}] oleh {$spvName}.");
            }

            $namaTeller = $pengajuan->user?->nama_petugas ?? 'Teller Loket';
            $spvName = Auth::user()->nama_petugas ?? 'Supervisor';

            // 1. Update status pengajuan kas
            $pengajuan->update([
                'status' => 'disetujui',
                'disetujui_oleh' => Auth::id(),
                'catatan_spv' => $catatan,
            ]);

            // 2. Tambahkan dana masuk ke kas brankas hari ini dengan locking
            $brankasHariIni = Brankas::whereDate('tanggal', $today)->lockForUpdate()->first();
            if (! $brankasHariIni) {
                $brankasHariIni = Brankas::getOrCreateToday($today, true);
            }

            $masuk = $brankasHariIni->masuk + $pengajuan->nominal;
            $total = $brankasHariIni->saldo_awal + $masuk - $brankasHariIni->keluar;
            $brankasHariIni->update([
                'masuk' => $masuk,
                'total' => $total,
            ]);

            // 3. Catat di Jurnal Akuntansi sebagai Droping Dana Kas Operasional
            Jurnal::create([
                'transaksi_id' => null,
                'keterangan' => "PENERIMAAN DROPING KAS OPERASIONAL [{$pengajuan->nomor_pengajuan}] - {$namaTeller} (Disetujui oleh {$spvName})",
                'debit' => $pengajuan->nominal,
                'kredit' => 0,
                'tanggal' => $today,
            ]);

            // 4. Catat jejak keputusan resmi ke approval_histories
            ApprovalHistory::record(
                pekerjaanType: 'pengajuan_kas',
                pekerjaanId: $pengajuan->id,
                nomorReferensi: $pengajuan->nomor_pengajuan,
                tellerId: $pengajuan->user_id,
                namaTeller: $namaTeller,
                jenisKeputusan: 'disetujui',
                statusSebelum: 'pending',
                statusSesudah: 'disetujui',
                nominal: $pengajuan->nominal,
                catatan: $catatan
            );

            // 5. Catat jejak audit
            AuditHelper::log(
                jenisAktivitas: 'SETUJUI_PENGAJUAN_KAS',
                modul: 'Pusat Persetujuan',
                objekId: $pengajuan->nomor_pengajuan,
                oldValues: ['status' => 'pending'],
                newValues: [
                    'status' => 'disetujui',
                    'disetujui_oleh' => Auth::id(),
                    'nominal' => $pengajuan->nominal,
                    'catatan_spv' => $catatan,
                ]
            );

            // 6. Kirim notifikasi konfirmasi ke Teller pemohon
            NotificationHelper::notifyUser($pengajuan->user_id, [
                'task_type' => 'pengajuan_kas',
                'task_id' => $pengajuan->id,
                'nomor_referensi' => $pengajuan->nomor_pengajuan,
                'nama_teller' => $namaTeller,
                'title' => 'Pengajuan Kas Disetujui',
                'message' => "Pengajuan tambahan kas #{$pengajuan->nomor_pengajuan} sebesar Rp ".number_format($pengajuan->nominal, 0, ',', '.')." telah DISETUJUI oleh {$spvName}.",
                'nominal' => $pengajuan->nominal,
                'status' => 'disetujui',
                'action_url' => url('/teller/pengajuan-kas'),
            ]);

            // Tandai notifikasi pekerjaan sebagai terbaca
            NotificationHelper::markTaskAsRead(Auth::user(), 'pengajuan_kas', $pengajuan->id);

            DB::commit();

            return redirect()->route('supervisor.pengajuan-kas.detail', $pengajuan->id)
                ->with('success', "Pengajuan kas #{$pengajuan->nomor_pengajuan} sebesar Rp ".number_format($pengajuan->nominal, 0, ',', '.').' BERHASIL DISETUJUI! Kas operasional loket otomatis bertambah.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses persetujuan pengajuan kas: '.$e->getMessage());
        }
    }

    /**
     * Supervisor / Accounting Menolak Pengajuan Kas (Wajib Menyertakan Alasan)
     */
    public function tolak(Request $request, int|string $id): RedirectResponse
    {
        $alasan = trim($request->alasan_penolakan ?? $request->catatan_spv ?? '');

        if (empty($alasan) || strlen($alasan) < 5) {
            return back()->withInput()
                ->withErrors(['alasan_penolakan' => 'Alasan penolakan pengajuan kas wajib diisi minimal 5 karakter agar dapat dipahami oleh Teller!'])
                ->with('error', 'Alasan penolakan pengajuan kas wajib diisi minimal 5 karakter agar dapat dipahami oleh Teller!');
        }

        DB::beginTransaction();
        try {
            // Row-level lock untuk integritas status
            $pengajuan = PengajuanKas::where('id', $id)->lockForUpdate()->first();

            if (! $pengajuan) {
                DB::rollBack();

                return back()->with('error', 'Data pengajuan kas tidak ditemukan!');
            }

            if ($pengajuan->status !== 'pending') {
                DB::rollBack();
                $spvName = $pengajuan->approver?->nama_petugas ?? 'Supervisor lain';

                return back()->with('error', "Pencegahan Dobel Penolakan: Pengajuan kas #{$pengajuan->nomor_pengajuan} sudah diproses sebelumnya dengan status [{$pengajuan->status}] oleh {$spvName}.");
            }

            $namaTeller = $pengajuan->user?->nama_petugas ?? 'Teller Loket';
            $spvName = Auth::user()->nama_petugas ?? 'Supervisor';

            $pengajuan->update([
                'status' => 'ditolak',
                'disetujui_oleh' => Auth::id(),
                'catatan_spv' => $alasan,
                'alasan_penolakan' => $alasan,
            ]);

            // Catat jejak keputusan ke approval_histories
            ApprovalHistory::record(
                pekerjaanType: 'pengajuan_kas',
                pekerjaanId: $pengajuan->id,
                nomorReferensi: $pengajuan->nomor_pengajuan,
                tellerId: $pengajuan->user_id,
                namaTeller: $namaTeller,
                jenisKeputusan: 'ditolak',
                statusSebelum: 'pending',
                statusSesudah: 'ditolak',
                nominal: $pengajuan->nominal,
                catatan: $alasan,
                alasanPenolakan: $alasan
            );

            // Log audit
            AuditHelper::log(
                jenisAktivitas: 'TOLAK_PENGAJUAN_KAS',
                modul: 'Pusat Persetujuan',
                objekId: $pengajuan->nomor_pengajuan,
                oldValues: ['status' => 'pending'],
                newValues: [
                    'status' => 'ditolak',
                    'disetujui_oleh' => Auth::id(),
                    'alasan_penolakan' => $alasan,
                ]
            );

            // Kirim notifikasi penolakan ke Teller pemohon
            NotificationHelper::notifyUser($pengajuan->user_id, [
                'task_type' => 'pengajuan_kas',
                'task_id' => $pengajuan->id,
                'nomor_referensi' => $pengajuan->nomor_pengajuan,
                'nama_teller' => $namaTeller,
                'title' => 'Pengajuan Kas Ditolak',
                'message' => "Pengajuan kas #{$pengajuan->nomor_pengajuan} telah DITOLAK oleh {$spvName}. Alasan: {$alasan}",
                'nominal' => $pengajuan->nominal,
                'status' => 'ditolak',
                'action_url' => url('/teller/pengajuan-kas'),
            ]);

            NotificationHelper::markTaskAsRead(Auth::user(), 'pengajuan_kas', $pengajuan->id);

            DB::commit();

            return redirect()->route('supervisor.pengajuan-kas.detail', $pengajuan->id)
                ->with('success', "Pengajuan kas #{$pengajuan->nomor_pengajuan} telah DITOLAK dengan alasan tercatat.");
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses penolakan pengajuan kas: '.$e->getMessage());
        }
    }

    /**
     * Fitur Atur / Input Kas Awal Operasional Hari Ini
     */
    public function setKasAwal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'saldo_awal' => 'required|numeric|min:0',
        ], [
            'saldo_awal.required' => 'Nominal saldo awal wajib diisi.',
            'saldo_awal.min' => 'Nominal saldo awal tidak boleh negatif.',
        ]);

        $today = Carbon::today()->toDateString();
        $saldoAwal = (int) $validated['saldo_awal'];

        DB::beginTransaction();
        try {
            $brankasHariIni = Brankas::whereDate('tanggal', $today)->lockForUpdate()->first();

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
