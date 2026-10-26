<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $hariIni = Carbon::today();

        $totalNasabah = DB::table('nasabah')->count();
        $totalPegawai = DB::table('users')->whereIn('role', ['teller', 'supervisor'])->count();
        $pegawai = DB::table('users')->get();

        $totalDebit = DB::table('jurnal')->whereDate('tanggal', $hariIni)->sum('debit');
        $totalKredit = DB::table('jurnal')->whereDate('tanggal', $hariIni)->sum('kredit');

        $mutasiNabung = DB::table('transaksi')->whereDate('created_at', $hariIni)->where('jenis_transaksi', 'setor')->where('status', '!=', 'dibatalkan')->sum('nominal');
        $mutasiTarik = DB::table('transaksi')->whereDate('created_at', $hariIni)->whereIn('jenis_transaksi', ['tarik', 'admin'])->where('status', '!=', 'dibatalkan')->sum('nominal');

        $isBalanced = ($totalDebit == $mutasiNabung) && ($totalKredit == $mutasiTarik);

        $totalVoid = DB::table('transaksi')->whereDate('updated_at', $hariIni)->where('status', 'dibatalkan')->count();
        $totalGagalSistem = 0;

        $resetRequests = DB::table('password_reset_requests')
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.dashboard', compact('totalNasabah', 'totalPegawai', 'pegawai', 'totalDebit', 'totalKredit', 'isBalanced', 'totalVoid', 'totalGagalSistem', 'resetRequests'));
    }

    public function create() { return view('admin.tambah'); }

    public function store(Request $request)
    {
        $request->validate([
            'nama_petugas' => 'required', 'username' => 'required|unique:users,username',
            'password' => 'required|min:6', 'role' => 'required|in:admin,teller,supervisor',
        ]);
        DB::table('users')->insert([
            'nama_petugas' => $request->nama_petugas, 'username' => $request->username,
            'password' => Hash::make($request->password), 'role' => $request->role,
            'kelas' => $request->kelas, 'no_hp' => $request->no_hp, 'email' => $request->email,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return redirect('/admin/dashboard')->with('success', 'Pegawai baru berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $pegawai = DB::table('users')->where('id', $id)->first();
        return view('admin.edit-pegawai', compact('pegawai'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['nama_petugas' => 'required', 'role' => 'required|in:admin,teller,supervisor']);
        $dataUpdate = [
            'nama_petugas' => $request->nama_petugas, 'role' => $request->role,
            'kelas' => $request->kelas, 'no_hp' => $request->no_hp, 'email' => $request->email, 'updated_at' => now(),
        ];
        if ($request->filled('password')) { $dataUpdate['password'] = Hash::make($request->password); }
        DB::table('users')->where('id', $id)->update($dataUpdate);
        return redirect('/admin/dashboard')->with('success', 'Data pegawai diperbarui!');
    }

    public function destroy($id)
    {
        DB::table('users')->where('id', $id)->delete();
        return redirect('/admin/dashboard')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function profil()
    {
        $user = Auth::user();
        return view('admin.profil', compact('user'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();
        $request->validate(['foto' => 'nullable|image|max:2048']);
        $dataUpdate = ['no_hp' => $request->no_hp, 'email' => $request->email, 'updated_at' => now()];
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public', $filename);
            $dataUpdate['foto'] = $filename;
        }
        DB::table('users')->where('id', $user->id)->update($dataUpdate);
        return back()->with('success', 'Profil diperbarui!');
    }

    // ==================================================================
    // FUNGSI BARU: JEJAK AUDIT & FILTER ANOMALI
    // ==================================================================
    public function auditLog(Request $request)
    {
        $query = DB::table('transaksi')
                    ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                    ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
                    ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis', 'users.nama_petugas', 'users.username');

        // Filter Target: NIS atau Teller
        if ($request->filled('target')) {
            $target = $request->target;
            $query->where(function($q) use ($target) {
                $q->where('nasabah.nis', 'like', "%{$target}%")
                  ->orWhere('users.nama_petugas', 'like', "%{$target}%")
                  ->orWhere('users.username', 'like', "%{$target}%");
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('transaksi.created_at', [$request->tgl_mulai . ' 00:00:00', $request->tgl_akhir . ' 23:59:59']);
        }

        // Filter Anomali: Di luar jam sekolah (Misal: sebelum 06:00 atau setelah 15:00)
        if ($request->has('luar_jam')) {
            $query->whereRaw('(TIME(transaksi.created_at) < "06:00:00" OR TIME(transaksi.created_at) > "15:00:00")');
        }

        $logs = $query->orderBy('transaksi.created_at', 'desc')->get();
        return view('admin.audit', compact('logs'));
    }

    // ==================================================================
    // FUNGSI BARU: EKSPOR DATA MENTAH KE CSV
    // ==================================================================
    public function exportAuditCsv(Request $request)
    {
        $query = DB::table('transaksi')
                    ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
                    ->leftJoin('users', 'transaksi.petugas_id', '=', 'users.id')
                    ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.nis', 'users.nama_petugas');

        if ($request->filled('target')) {
            $target = $request->target;
            $query->where(function($q) use ($target) {
                $q->where('nasabah.nis', 'like', "%{$target}%")
                  ->orWhere('users.nama_petugas', 'like', "%{$target}%");
            });
        }
        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('transaksi.created_at', [$request->tgl_mulai . ' 00:00:00', $request->tgl_akhir . ' 23:59:59']);
        }
        if ($request->has('luar_jam')) {
            $query->whereRaw('(TIME(transaksi.created_at) < "06:00:00" OR TIME(transaksi.created_at) > "15:00:00")');
        }

        $logs = $query->orderBy('transaksi.created_at', 'desc')->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Audit_Trail_EBank_" . now()->format('Ymd_His') . ".csv",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            // Header Kolom Excel
            fputcsv($file, ['ID Transaksi', 'Tanggal & Waktu', 'Petugas (Teller)', 'NIS Nasabah', 'Nama Nasabah', 'Jenis Transaksi', 'Nominal (Rp)', 'Status', 'Alasan Batal (Jika Void)']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                    $log->nama_petugas ?? 'Sistem',
                    $log->nis,
                    $log->nama_siswa,
                    strtoupper($log->jenis_transaksi),
                    $log->nominal,
                    strtoupper($log->status),
                    $log->alasan_batal ?? '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function approveResetRequest(Request $request, $id)
    {
        $item = DB::table('password_reset_requests')->where('id', $id)->first();
        if (!$item) {
            return back()->with('error', 'Permohonan reset password tidak ditemukan.');
        }

        $user = DB::table('users')->where('username', $item->username)->first();
        if (!$user) {
            return back()->with('error', 'User akun "' . $item->username . '" tidak ditemukan.');
        }

        $newPassword = $request->input('new_password', 'bankmini' . date('Y'));

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($newPassword),
            'updated_at' => now(),
        ]);

        $adminName = Auth::user()->nama_petugas ?? 'Administrator';

        DB::table('password_reset_requests')->where('id', $id)->update([
            'status' => 'completed',
            'keterangan' => 'Disetujui oleh ' . $adminName . '. Password di-reset menjadi: ' . $newPassword,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Permohonan disetujui! Kata sandi akun ' . $item->username . ' berhasil direset menjadi: ' . $newPassword);
    }

    public function rejectResetRequest(Request $request, $id)
    {
        $adminName = Auth::user()->nama_petugas ?? 'Administrator';
        $alasan = $request->input('alasan', 'Data tidak sesuai atau permohonan ditolak.');

        DB::table('password_reset_requests')->where('id', $id)->update([
            'status' => 'rejected',
            'keterangan' => 'Ditolak oleh ' . $adminName . ' (Alasan: ' . $alasan . ')',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Permohonan reset password berhasil ditolak.');
    }
}
