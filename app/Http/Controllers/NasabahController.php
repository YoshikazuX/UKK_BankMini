<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\TerbilangHelper;

class NasabahController extends Controller
{
    /**
     * Tampilkan Daftar Nasabah dengan Filter Alumni & Kategori
     */
    public function index(Request $request)
    {
        $query = DB::table('nasabah');

        // Filter Status Siswa (Aktif / Alumni) untuk mengatasi Database Bloat
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            // Default tampilkan yang aktif jika tidak difilter 'all'
            if (!$request->has('status')) {
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
            if (preg_match('/\bXII\b/', $k)) $grade = 3;
            elseif (preg_match('/\bXI\b/', $k)) $grade = 2;
            elseif (preg_match('/\bX\b/', $k)) $grade = 1;

            // 2. Deteksi Jurusan (Sesuai Urutan)
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

            return sprintf('%02d-%02d-%02d-%010s', $grade, $jurusan, $sub, $item->nis);
        })->values();

        // Rekap Statistik Status Nasabah
        $totalAktif = DB::table('nasabah')->where('status', 'aktif')->count();
        $totalAlumni = DB::table('nasabah')->where('status', 'alumni')->count();
        $totalGuru = DB::table('nasabah')->where('kategori', 'guru_karyawan')->count();

        return view('admin.data-nasabah', compact('nasabah', 'totalAktif', 'totalAlumni', 'totalGuru'));
    }

    public function create()
    {
        return view('admin.tambah-nasabah');
    }

    /**
     * Simpan Nasabah Baru dengan Perumusan Otomatis ID Rekening Armadillo
     * (Kode Jurusan - No Urut 3 Digit)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:nasabah,nis',
            'nama_siswa' => 'required|string|max:100',
            'kelas' => 'required|string|max:50',
            'jurusan' => 'required|string|max:100',
            'kategori' => 'nullable|string',
        ]);

        $kategori = $request->kategori ?? 'siswa';
        $no_rekening = self::generateNoRekening($request->jurusan, $kategori);

        DB::table('nasabah')->insert([
            'no_rekening' => $no_rekening,
            'nis' => $request->nis,
            'nama_siswa' => $request->nama_siswa,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'username' => $request->nis,
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 0,
            'status' => 'aktif',
            'kategori' => $kategori,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/admin/data-nasabah')->with('success', "Nasabah baru berhasil didaftarkan dengan No. Rekening Armadillo: {$no_rekening}!");
    }

    public function edit(int|string $id)
    {
        $nasabah = DB::table('nasabah')->where('id', $id)->first();
        return view('admin.edit-nasabah', compact('nasabah'));
    }

    public function update(Request $request, int|string $id)
    {
        $request->validate([
            'nama_siswa' => 'required',
            'kelas' => 'required',
            'jurusan' => 'required',
            'status' => 'nullable|in:aktif,alumni,nonaktif',
            'kategori' => 'nullable|in:siswa,guru_karyawan',
        ]);

        $data = [
            'nama_siswa' => $request->nama_siswa,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'updated_at' => now(),
        ];

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }
        if ($request->filled('kategori')) {
            $data['kategori'] = $request->kategori;
        }

        DB::table('nasabah')->where('id', $id)->update($data);

        return redirect('/admin/data-nasabah')->with('success', 'Data nasabah berhasil diperbarui!');
    }

    public function destroy(int|string $id)
    {
        DB::table('nasabah')->where('id', $id)->delete();
        return redirect('/admin/data-nasabah')->with('success', 'Nasabah berhasil dihapus!');
    }

    /**
     * Rumus ID Rekening Armadillo:
     * (Kode Jurusan - No Urut Zero Padding 3 Digit)
     */
    /**
     * Algoritma Pembuatan Nomor Rekening Baru Otomatis
     * Berbasis Urutan Tahun Ajaran dan Nomor Urut Siswa (Contoh: 26-RPL-001 atau 2026/RPL/001)
     */
    public static function generateNoRekening(string $jurusan, string $kategori = 'siswa'): string
    {
        $kode = ($kategori === 'guru_karyawan') ? 'GK' : TerbilangHelper::getKodeJurusan($jurusan);
        $setting = \App\Helpers\SettingHelper::getSettings();
        $format = $setting->format_no_rekening ?? 'TA-JURUSAN-URUT';
        $ta = $setting->tahun_ajaran_aktif ?? '2026/2027';

        // Ekstrak 2 digit atau 4 digit tahun ajaran
        preg_match('/(\d{4})/', $ta, $m);
        $fullYear = isset($m[1]) ? $m[1] : date('Y');
        $ta2Digit = substr($fullYear, -2);

        // Pola awalan nomor rekening
        $prefixPola = match($format) {
            'TAHUN/JURUSAN/URUT' => "{$fullYear}/{$kode}/",
            'JURUSAN-URUT' => "{$kode}-",
            default => "{$ta2Digit}-{$kode}-",
        };

        // Ambil nasabah terakhir dengan format ini
        $lastRecord = DB::table('nasabah')
            ->where('no_rekening', 'LIKE', $prefixPola . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->no_rekening, $matches)) {
            $nextNum = (int)$matches[1] + 1;
        } else {
            // Kompatibilitas mundur dengan format lama (contoh: RPL-001)
            $lastOld = DB::table('nasabah')->where('no_rekening', 'LIKE', $kode . '-%')->orderBy('id', 'desc')->first();
            if ($lastOld && preg_match('/-(\d+)$/', $lastOld->no_rekening, $mOld)) {
                $nextNum = (int)$mOld[1] + 1;
            } else {
                $count = DB::table('nasabah')->where('jurusan', 'LIKE', "%{$jurusan}%")->count();
                $nextNum = max(1, $count + 1);
            }
        }

        // Pastikan tidak duplikat
        do {
            $candidate = $prefixPola . sprintf('%03d', $nextNum);
            $exists = DB::table('nasabah')->where('no_rekening', $candidate)->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        return $candidate;
    }

    // FUNGSI IMPORT DARI EXCEL (CSV)
    public function importCsv(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file_csv');
        $handle = fopen($file->getPathname(), "r");
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
                if ($header) { $header = false; continue; }

                if(!isset($row[0]) || !isset($row[1])) continue;

                $nis = trim($row[0]);
                $nama = trim($row[1]);
                $kelas = isset($row[2]) ? trim($row[2]) : '-';
                $jurusan = isset($row[3]) ? trim($row[3]) : 'Rekayasa Perangkat Lunak';

                $exists = DB::table('nasabah')->where('nis', $nis)->first();
                if (!$exists && !empty($nis)) {
                    $no_rekening = self::generateNoRekening($jurusan, 'siswa');

                    DB::table('nasabah')->insert([
                        'no_rekening' => $no_rekening,
                        'nis' => $nis,
                        'nama_siswa' => $nama,
                        'kelas' => $kelas,
                        'jurusan' => $jurusan,
                        'username' => $nis,
                        'password' => Hash::make('123456'),
                        'pin_keamanan' => Hash::make('123456'),
                        'saldo' => 0,
                        'status' => 'aktif',
                        'kategori' => 'siswa',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $sukses++;
                } else {
                    $gagal++;
                }
            }
            fclose($handle);
            DB::commit();
            return back()->with('success', "Import Selesai! $sukses data masuk dengan Nomor Rekening Armadillo otomatis, $gagal data diabaikan (NIS kembar/kosong).");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal impor data: ' . $e->getMessage());
        }
    }

    // FUNGSI DOWNLOAD TEMPLATE EXCEL (CSV)
    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_import_nasabah.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NIS', 'Nama Lengkap', 'Kelas (Contoh: XII RPL 1)', 'Jurusan (Contoh: Rekayasa Perangkat Lunak)'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
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
    public function dashboardNasabah(Request $request)
    {
        $user = Auth::user();
        $username = $user->username ?? '';
        
        $nasabah = DB::table('nasabah')
            ->where('username', $username)
            ->orWhere('nis', $username)
            ->first();
            
        if (!$nasabah) {
            $nasabah = DB::table('nasabah')->first();
        }

        $transaksi = [];
        if ($nasabah) {
            $transaksi = DB::table('transaksi')
                ->where('nasabah_id', $nasabah->id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('nasabah.dashboard', compact('nasabah', 'transaksi'));
    }
}
