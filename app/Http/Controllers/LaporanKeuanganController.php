<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanKeuanganController extends Controller
{
    /**
     * Laporan Keuangan Jurnal Akuntansi dengan Filter Wajib (Tanggal, Teller, Nasabah, Rekening)
     */
    public function jurnalAkuntansi(Request $request): View|Response
    {
        $query = Jurnal::with(['transaksi.nasabah', 'transaksi.petugas']);

        // Filter Rentang Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        // Filter Nama Teller
        if ($request->filled('nama_teller')) {
            $teller = $request->nama_teller;
            $query->whereHas('transaksi.petugas', fn ($q) => $q->where('nama_petugas', 'LIKE', "%{$teller}%"));
        }

        // Filter Nama Nasabah
        if ($request->filled('nama_nasabah')) {
            $nama = $request->nama_nasabah;
            $query->whereHas('transaksi.nasabah', fn ($q) => $q->where('nama_siswa', 'LIKE', "%{$nama}%"));
        }

        // Filter Nomor Rekening / NIS
        if ($request->filled('rekening')) {
            $rek = $request->rekening;
            $query->whereHas('transaksi.nasabah', function ($q) use ($rek) {
                $q->where('no_rekening', 'LIKE', "%{$rek}%")
                    ->orWhere('nis', 'LIKE', "%{$rek}%");
            });
        }

        $jurnal = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        // Siapkan properti tambahan untuk kompatibilitas tampilan Blade
        $jurnal->each(function ($item) {
            $item->jenis_transaksi = $item->transaksi->jenis_transaksi ?? null;
            $item->kode_sandi = $item->transaksi->kode_sandi ?? null;
            $item->nama_siswa = $item->transaksi->nasabah->nama_siswa ?? null;
            $item->nis = $item->transaksi->nasabah->nis ?? null;
            $item->no_rekening = $item->transaksi->nasabah->no_rekening ?? null;
            $item->nama_teller = $item->transaksi->petugas->nama_petugas ?? null;
        });

        $totalDebit = $jurnal->sum('debit');
        $totalKredit = $jurnal->sum('kredit');
        $isBalanced = ($totalDebit == $totalKredit);

        $role = strtolower(Auth::user()->role ?? 'accounting');

        if ($request->input('export') === 'excel') {
            return $this->exportExcelJurnal($jurnal, $totalDebit, $totalKredit);
        }

        if ($request->input('export') === 'print') {
            return view('laporan.cetak-jurnal', compact('jurnal', 'totalDebit', 'totalKredit', 'isBalanced'));
        }

        return view('laporan.jurnal', compact('jurnal', 'totalDebit', 'totalKredit', 'isBalanced', 'role'));
    }

    /**
     * Laporan Saldo Simpanan Per Produk (Tabungan Siswa vs Tabungan Guru/Karyawan)
     */
    public function saldoPerProduk(Request $request): View
    {
        $siswaList = Nasabah::where('kategori', 'siswa')->get();
        $guruList = Nasabah::where('kategori', 'guru_karyawan')->get();

        $totalSiswaSaldo = $siswaList->sum('saldo');
        $totalGuruSaldo = $guruList->sum('saldo');
        $grandTotal = $totalSiswaSaldo + $totalGuruSaldo;

        if ($request->input('export') === 'print') {
            return view('laporan.cetak-saldo-produk', compact('siswaList', 'guruList', 'totalSiswaSaldo', 'totalGuruSaldo', 'grandTotal'));
        }

        $role = strtolower(Auth::user()->role ?? 'accounting');

        return view('laporan.saldo-produk', compact(
            'siswaList',
            'guruList',
            'totalSiswaSaldo',
            'totalGuruSaldo',
            'grandTotal',
            'role'
        ));
    }

    /**
     * Laporan Tahunan Rekapitulasi Kas & Transaksi Per Bulan
     */
    public function laporanTahunan(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        $rekapBulanan = Transaksi::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('SUM(CASE WHEN jenis_transaksi IN ("setor", "koreksi_tarik") THEN nominal ELSE 0 END) as total_setor'),
            DB::raw('SUM(CASE WHEN jenis_transaksi IN ("tarik", "koreksi_setor") THEN nominal ELSE 0 END) as total_tarik'),
            DB::raw('SUM(CASE WHEN jenis_transaksi = "admin" THEN nominal ELSE 0 END) as total_admin'),
            DB::raw('COUNT(*) as jumlah_transaksi')
        )
            ->whereYear('created_at', $tahun)
            ->where('status', '!=', 'dibatalkan')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('bulan', 'asc')
            ->get();

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($request->input('export') === 'print') {
            return view('laporan.cetak-tahunan', compact('rekapBulanan', 'namaBulan', 'tahun'));
        }

        $role = strtolower(Auth::user()->role ?? 'accounting');

        return view('laporan.tahunan', compact('rekapBulanan', 'namaBulan', 'tahun', 'role'));
    }

    /**
     * Ekspor Format Spreadsheet Excel (.xls yang berformat rapi, bukan CSV mentah)
     */
    private function exportExcelJurnal(mixed $jurnal, float|int $totalDebit, float|int $totalKredit): Response
    {
        $filename = 'Jurnal_Akuntansi_Bank_Mini_'.date('Y-m-d_His').'.xls';

        $output = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $output .= '<head><meta charset="utf-8">';
        $output .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Jurnal Akuntansi</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $output .= '<style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
            th { background-color: #1e3a8a; color: #ffffff; border: 1px solid #000; padding: 8px; text-align: center; }
            td { border: 1px solid #94a3b8; padding: 6px; font-size: 11px; }
            .header-title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 10px; }
            .currency { text-align: right; }
        </style>';
        $output .= '</head><body>';

        $output .= '<table>';
        $output .= '<tr><td colspan="8" class="header-title">LKM MITRA SISWA ABADI - JURNAL AKUNTANSI BANK MINI</td></tr>';
        $output .= '<tr><td colspan="8" style="text-align:center; font-size:11px; color:#555;">Dicetak pada: '.date('d/m/Y H:i').' WIB</td></tr>';
        $output .= '<tr><td colspan="8"></td></tr>';

        $output .= '<tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No. Rekening</th>
            <th>Nama Nasabah</th>
            <th>Keterangan / Sandi</th>
            <th>Teller / Avalis</th>
            <th>Debit (Rp)</th>
            <th>Kredit (Rp)</th>
        </tr>';

        $no = 1;
        foreach ($jurnal as $j) {
            $output .= '<tr>';
            $output .= '<td style="text-align:center;">'.$no++.'</td>';
            $output .= '<td style="text-align:center;">'.date('d/m/Y', strtotime($j->tanggal)).'</td>';
            $output .= '<td style="mso-number-format:\'@\';">'.($j->no_rekening ?? $j->nis ?? '-').'</td>';
            $output .= '<td>'.($j->nama_siswa ?? '-').'</td>';
            $output .= '<td>'.$j->keterangan.'</td>';
            $output .= '<td>'.($j->nama_teller ?? 'Teller').'</td>';
            $output .= '<td class="currency">'.number_format($j->debit, 0, ',', '.').'</td>';
            $output .= '<td class="currency">'.number_format($j->kredit, 0, ',', '.').'</td>';
            $output .= '</tr>';
        }

        $output .= '<tr style="font-weight:bold; background-color:#f1f5f9;">';
        $output .= '<td colspan="6" style="text-align:right;">TOTAL:</td>';
        $output .= '<td class="currency">Rp '.number_format($totalDebit, 0, ',', '.').'</td>';
        $output .= '<td class="currency">Rp '.number_format($totalKredit, 0, ',', '.').'</td>';
        $output .= '</tr>';

        $output .= '</table>';
        $output .= '</body></html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
