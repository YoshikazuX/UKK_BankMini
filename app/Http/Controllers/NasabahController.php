<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Helpers\TerbilangHelper;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NasabahController extends Controller
{
    /**
     * Tampilkan Daftar Nasabah dengan Filter Alumni & Kategori
     */
    public function index(Request $request): View
    {
        $query = Nasabah::query();

        // Filter Status Siswa (Aktif / Alumni)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            if (! $request->has('status')) {
                $query->where('status', 'aktif');
            }
        }

        // Filter Kategori (Siswa vs Guru/Karyawan)
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter Pencarian
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($sub) use ($cari) {
                $sub->where('nis', 'LIKE', "%{$cari}%")
                    ->orWhere('nama_siswa', 'LIKE', "%{$cari}%")
                    ->orWhere('no_rekening', 'LIKE', "%{$cari}%");
            });
        }

        $nasabah = $query->get();

        // ALGORITMA SORTING PINTAR UNTUK ADMIN (Tingkat -> Jurusan -> SubKelas -> NIS)
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

            return sprintf('%02d-%02d-%02d-%010s', $grade, $jurusan, $sub, $item->nis);
        })->values();

        // Rekap Statistik Status Nasabah
        $totalAktif = Nasabah::where('status', 'aktif')->count();
        $totalAlumni = Nasabah::where('status', 'alumni')->count();
        $totalGuru = Nasabah::where('kategori', 'guru_karyawan')->count();

        return view('admin.data-nasabah', compact('nasabah', 'totalAktif', 'totalAlumni', 'totalGuru'));
    }

    public function create(): View
    {
        return view('admin.tambah-nasabah');
    }

    /**
     * Simpan Nasabah Baru dengan Perumusan Otomatis ID Rekening Armadillo
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => 'required|unique:nasabah,nis',
            'nama_siswa' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'jurusan' => 'required|string|max:100',
            'kategori' => 'nullable|string',
        ]);

        $kategori = $validated['kategori'] ?? 'siswa';
        $no_rekening = self::generateNoRekening($validated['jurusan'], $kategori);

        Nasabah::create([
            'no_rekening' => $no_rekening,
            'nis' => $validated['nis'],
            'nama_siswa' => $validated['nama_siswa'],
            'kelas' => $validated['kelas'],
            'jurusan' => $validated['jurusan'],
            'username' => $validated['nis'],
            'password' => '123456',
            'pin_keamanan' => '123456',
            'saldo' => 0,
            'status' => 'aktif',
            'kategori' => $kategori,
        ]);

        return redirect('/admin/data-nasabah')->with('success', "Nasabah baru berhasil didaftarkan dengan No. Rekening Armadillo: {$no_rekening}!");
    }

    public function edit(int|string $id): View
    {
        $nasabah = Nasabah::findOrFail($id);

        return view('admin.edit-nasabah', compact('nasabah'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $nasabah = Nasabah::findOrFail($id);

        $validated = $request->validate([
            'nama_siswa' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'jurusan' => 'required|string|max:100',
            'status' => 'nullable|in:aktif,alumni,nonaktif',
            'kategori' => 'nullable|in:siswa,guru_karyawan',
        ]);

        $nasabah->update($validated);

        return redirect('/admin/data-nasabah')->with('success', 'Data nasabah berhasil diperbarui!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $nasabah = Nasabah::findOrFail($id);
        $nasabah->delete();

        return redirect('/admin/data-nasabah')->with('success', 'Nasabah berhasil dihapus!');
    }

    /**
     * Algoritma Pembuatan Nomor Rekening Baru Otomatis
     * Berbasis Urutan Tahun Ajaran dan Nomor Urut Siswa (Contoh: 26-RPL-001 atau 2026/RPL/001)
     */
    public static function generateNoRekening(string $jurusan, string $kategori = 'siswa'): string
    {
        $kode = ($kategori === 'guru_karyawan') ? 'GK' : TerbilangHelper::getKodeJurusan($jurusan);
        $setting = SettingHelper::getSettings();
        $format = $setting->format_no_rekening ?? 'TA-JURUSAN-URUT';
        $ta = $setting->tahun_ajaran_aktif ?? '2026/2027';

        // Ekstrak 2 digit atau 4 digit tahun ajaran
        preg_match('/(\d{4})/', $ta, $m);
        $fullYear = $m[1] ?? date('Y');
        $ta2Digit = substr($fullYear, -2);

        // Pola awalan nomor rekening
        $prefixPola = match ($format) {
            'TAHUN/JURUSAN/URUT' => "{$fullYear}/{$kode}/",
            'JURUSAN-URUT' => "{$kode}-",
            default => "{$ta2Digit}-{$kode}-",
        };

        // Ambil nasabah terakhir dengan format ini
        $lastRecord = Nasabah::where('no_rekening', 'LIKE', $prefixPola.'%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->no_rekening, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        } else {
            // Kompatibilitas mundur dengan format lama (contoh: RPL-001)
            $lastOld = Nasabah::where('no_rekening', 'LIKE', $kode.'-%')->orderBy('id', 'desc')->first();
            if ($lastOld && preg_match('/-(\d+)$/', $lastOld->no_rekening, $mOld)) {
                $nextNum = (int) $mOld[1] + 1;
            } else {
                $count = Nasabah::where('jurusan', 'LIKE', "%{$jurusan}%")->count();
                $nextNum = max(1, $count + 1);
            }
        }

        // Pastikan tidak duplikat
        do {
            $candidate = $prefixPola.sprintf('%03d', $nextNum);
            $exists = Nasabah::where('no_rekening', $candidate)->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        return $candidate;
    }

    // FUNGSI IMPORT DARI EXCEL (CSV)
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file_csv');
        $handle = fopen($file->getPathname(), 'r');
        $header = true;
        $sukses = 0;
        $gagal = 0;

        // Auto-detect delimiter (; or ,)
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if ($header) {
                    $header = false;

                    continue;
                }

                if (! isset($row[0]) || ! isset($row[1])) {
                    continue;
                }

                $nis = trim($row[0]);
                $nama = trim($row[1]);
                $kelas = isset($row[2]) ? trim($row[2]) : '-';
                $jurusan = isset($row[3]) ? trim($row[3]) : 'Rekayasa Perangkat Lunak';

                $exists = Nasabah::where('nis', $nis)->first();
                if (! $exists && ! empty($nis)) {
                    $no_rekening = self::generateNoRekening($jurusan, 'siswa');

                    Nasabah::create([
                        'no_rekening' => $no_rekening,
                        'nis' => $nis,
                        'nama_siswa' => $nama,
                        'kelas' => $kelas,
                        'jurusan' => $jurusan,
                        'username' => $nis,
                        'password' => '123456',
                        'pin_keamanan' => '123456',
                        'saldo' => 0,
                        'status' => 'aktif',
                        'kategori' => 'siswa',
                    ]);
                    $sukses++;
                } else {
                    $gagal++;
                }
            }
            fclose($handle);
            DB::commit();

            return back()->with('success', "Import Selesai! {$sukses} data masuk dengan Nomor Rekening Armadillo otomatis, {$gagal} data diabaikan (NIS kembar/kosong).");
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal impor data: '.$e->getMessage());
        }
    }

    // FUNGSI DOWNLOAD TEMPLATE EXCEL (CSV)
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=template_import_nasabah.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['NIS', 'Nama Lengkap', 'Kelas (Contoh: XII RPL 1)', 'Jurusan (Contoh: Rekayasa Perangkat Lunak)'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');
            fputcsv($file, ['17763', 'Dewi Handayani Nur Halimah', 'XI RPL 1', 'Rekayasa Perangkat Lunak'], ';');
            fputcsv($file, ['17764', 'Bima Revan Saputra', 'XII RPL 2', 'Rekayasa Perangkat Lunak'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Dashboard Portal Nasabah / Siswa
     */
    public function dashboardNasabah(Request $request): View
    {
        $user = Auth::user();
        $username = $user->username ?? '';

        $nasabah = Nasabah::where('username', $username)
            ->orWhere('nis', $username)
            ->first();

        if (! $nasabah) {
            $nasabah = Nasabah::first();
        }

        $transaksi = [];
        if ($nasabah) {
            $transaksi = Transaksi::where('nasabah_id', $nasabah->id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('nasabah.dashboard', compact('nasabah', 'transaksi'));
    }
}
