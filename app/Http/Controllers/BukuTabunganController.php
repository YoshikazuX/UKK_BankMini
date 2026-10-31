<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\TerbilangHelper;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BukuTabunganController extends Controller
{
    /**
     * Tampilkan Halaman Buku Tabungan Fisik / Buku Besar Pembantu Nasabah
     */
    public function show(int|string $id): View|RedirectResponse
    {
        $nasabah = Nasabah::find($id);
        if (! $nasabah) {
            return back()->with('error', 'Data nasabah tidak ditemukan!');
        }

        // Ambil seluruh riwayat mutasi transaksi nasabah
        $transaksi = Transaksi::with('petugas')
            ->where('nasabah_id', $id)
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung Saldo Berjalan (Running Balance) untuk format buku tabungan
        $saldoBerjalan = 0;
        $totalSetor = 0;
        $totalTarik = 0;

        foreach ($transaksi as $trx) {
            $trx->nama_teller = $trx->petugas->nama_petugas ?? 'Teller';
            if ($trx->jenis_transaksi === 'setor') {
                $saldoBerjalan += $trx->nominal;
                $totalSetor += $trx->nominal;
            } else {
                $saldoBerjalan -= $trx->nominal;
                $totalTarik += $trx->nominal;
            }
            $trx->saldo_berjalan = $saldoBerjalan;
        }

        $role = Auth::user()->role ?? 'teller';

        return view('nasabah.buku-tabungan', compact(
            'nasabah',
            'transaksi',
            'totalSetor',
            'totalTarik',
            'role'
        ));
    }

    /**
     * Cetak Lembar Buku Tabungan (Ukuran Buku Bank Mini Biru)
     */
    public function cetakBuku(int|string $id): View|RedirectResponse
    {
        $nasabah = Nasabah::find($id);
        if (! $nasabah) {
            return back()->with('error', 'Data nasabah tidak ditemukan!');
        }

        $transaksi = Transaksi::with('petugas')
            ->where('nasabah_id', $id)
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('created_at', 'asc')
            ->get();

        $saldoBerjalan = 0;
        foreach ($transaksi as $trx) {
            $trx->nama_teller = $trx->petugas->nama_petugas ?? 'Teller';
            if ($trx->jenis_transaksi === 'setor') {
                $saldoBerjalan += $trx->nominal;
            } else {
                $saldoBerjalan -= $trx->nominal;
            }
            $trx->saldo_berjalan = $saldoBerjalan;
        }

        return view('nasabah.cetak-buku', compact('nasabah', 'transaksi'));
    }

    /**
     * Cetak Slip Setoran Tunai (2 Lembar Rangkap: Arsip Bank Mini & Nasabah)
     */
    public function cetakSlipSetor(int|string $id): View|RedirectResponse
    {
        $trx = Transaksi::with(['nasabah', 'petugas'])->find($id);

        if (! $trx) {
            return back()->with('error', 'Data transaksi tidak ditemukan!');
        }

        $trx->nama_siswa = $trx->nasabah->nama_siswa ?? '-';
        $trx->no_rekening = $trx->nasabah->no_rekening ?? '-';
        $trx->nis = $trx->nasabah->nis ?? '-';
        $trx->kelas = $trx->nasabah->kelas ?? '-';
        $trx->jurusan = $trx->nasabah->jurusan ?? '-';
        $trx->nama_teller = $trx->petugas->nama_petugas ?? 'Teller';

        $terbilang = TerbilangHelper::terbilang($trx->nominal);

        return view('teller.slip-setoran', compact('trx', 'terbilang'));
    }

    /**
     * Cetak Slip Penarikan Tunai
     */
    public function cetakSlipTarik(int|string $id): View|RedirectResponse
    {
        $trx = Transaksi::with(['nasabah', 'petugas'])->find($id);

        if (! $trx) {
            return back()->with('error', 'Data transaksi tidak ditemukan!');
        }

        $trx->nama_siswa = $trx->nasabah->nama_siswa ?? '-';
        $trx->no_rekening = $trx->nasabah->no_rekening ?? '-';
        $trx->nis = $trx->nasabah->nis ?? '-';
        $trx->kelas = $trx->nasabah->kelas ?? '-';
        $trx->jurusan = $trx->nasabah->jurusan ?? '-';
        $trx->nama_teller = $trx->petugas->nama_petugas ?? 'Teller';

        $terbilang = TerbilangHelper::terbilang($trx->nominal);

        return view('teller.slip-penarikan', compact('trx', 'terbilang'));
    }

    /**
     * Ubah Status Nasabah (Aktif vs Alumni untuk cegah database bloat)
     */
    public function ubahStatus(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:aktif,alumni,nonaktif',
        ]);

        $nasabah = Nasabah::findOrFail($id);
        $oldStatus = $nasabah->status;
        $nasabah->update(['status' => $validated['status']]);

        AuditHelper::log(
            jenisAktivitas: 'UBAH_STATUS_REKENING',
            modul: 'Rekening',
            objekId: $nasabah->no_rekening,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $nasabah->status]
        );

        $statusLabel = match ($nasabah->status) {
            'aktif' => 'diaktifkan',
            'nonaktif' => 'dinonaktifkan',
            'alumni' => 'diarsipkan sebagai alumni',
            default => $nasabah->status,
        };

        return back()->with('success', "Status rekening nasabah {$nasabah->nama_siswa} berhasil {$statusLabel}!");
    }
}
