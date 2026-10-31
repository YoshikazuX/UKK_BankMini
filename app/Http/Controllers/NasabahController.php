<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
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
     * Tampilkan Daftar Nasabah dengan Pencarian & Multi-Filter Terpadu
     */
    public function index(Request $request): View
    {
        $query = Nasabah::query();

        // 1. Pencarian Bebas (Nama Nasabah, No Rekening, NIS/NIP)
        if ($request->filled('cari')) {
            $cari = trim($request->cari);
            $query->where(function ($sub) use ($cari) {
                $sub->where('nama_siswa', 'like', "%{$cari}%")
                    ->orWhere('no_rekening', 'like', "%{$cari}%")
                    ->orWhere('nis', 'like', "%{$cari}%");
            });
        }

        // 2. Filter Kelas / Rombongan Belajar
        if ($request->filled('kelas') && $request->kelas !== 'all') {
            $query->where('kelas', $request->kelas);
        }

        // 3. Filter Status Rekening: Aktif, Nonaktif, Alumni
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 4. Filter Kategori Nasabah: Siswa, Guru, Alumni
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $kat = strtolower($request->kategori);
            if ($kat === 'guru') {
                $query->where(function ($sub) {
                    $sub->where('kategori', 'guru')
                        ->orWhere('kategori', 'guru_karyawan');
                });
            } elseif ($kat === 'alumni') {
                $query->where(function ($sub) {
                    $sub->where('kategori', 'alumni')
                        ->orWhere('status', 'alumni');
                });
            } else {
                $query->where('kategori', $kat);
            }
        }

        // Hitung total hasil pencarian
        $totalHasil = (clone $query)->count();

        // Pagination 15 per halaman dengan parameter filter tetap terjaga
        $nasabah = $query->orderBy('nama_siswa', 'asc')->paginate(15)->withQueryString();

        // Daftar kelas unik dari database untuk pilihan dropdown filter
        $daftarKelas = Nasabah::whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas');

        // Statistik Ringkasan Nasabah
        $totalSemua = Nasabah::count();
        $totalAktif = Nasabah::where('status', 'aktif')->count();
        $totalNonaktif = Nasabah::where('status', 'nonaktif')->count();
        $totalAlumni = Nasabah::where('status', 'alumni')->count();
        $totalGuru = Nasabah::whereIn('kategori', ['guru', 'guru_karyawan'])->count();

        return view('admin.data-nasabah', compact(
            'nasabah',
            'daftarKelas',
            'totalHasil',
            'totalSemua',
            'totalAktif',
            'totalNonaktif',
            'totalAlumni',
            'totalGuru'
        ));
    }

    /**
     * Halaman Detail Nasabah Komprehensif
     */
    public function show(int|string $id): View
    {
        $nasabah = Nasabah::findOrFail($id);

        $minMengendap = (float) SettingHelper::getSaldoMengendapMin();
        $saldoSaatIni = (float) $nasabah->saldo;
        $saldoDapatDitarik = max(0, $saldoSaatIni - $minMengendap);

        // Riwayat transaksi resmi dari tabel transaksi dengan pagination bertahap
        $transaksi = Transaksi::with('petugas')
            ->where('nasabah_id', $nasabah->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.nasabah.show', compact(
            'nasabah',
            'minMengendap',
            'saldoSaatIni',
            'saldoDapatDitarik',
            'transaksi'
        ));
    }

    public function create(): View
    {
        return view('admin.tambah-nasabah');
    }

    /**
     * Simpan Nasabah Baru dengan Perumusan Otomatis ID Rekening
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:nasabah,nis',
            'nama_siswa' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'jurusan' => 'required|string|max:100',
            'kategori' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:100|unique:nasabah,username',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|email|max:150',
            'no_hp' => 'nullable|string|max:25',
        ]);

        $kategori = $validated['kategori'] ?? 'siswa';
        $no_rekening = $request->filled('no_rekening') ? $request->no_rekening : self::generateNoRekening($validated['jurusan'], $kategori);
        $username = $validated['username'] ?? $validated['nis'];
        $password = $validated['password'] ?? '123456';

        $nasabah = Nasabah::create([
            'no_rekening' => $no_rekening,
            'nis' => $validated['nis'],
            'nama_siswa' => $validated['nama_siswa'],
            'kelas' => $validated['kelas'],
            'jurusan' => $validated['jurusan'],
            'username' => $username,
            'email' => $validated['email'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'password' => $password,
            'pin_keamanan' => '123456',
            'saldo' => 0,
            'status' => 'aktif',
            'kategori' => $kategori,
        ]);

        AuditHelper::log(
            jenisAktivitas: 'TAMBAH_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: null,
            newValues: [
                'no_rekening' => $nasabah->no_rekening,
                'nis' => $nasabah->nis,
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'jurusan' => $nasabah->jurusan,
                'kategori' => $nasabah->kategori,
                'status' => $nasabah->status,
                'email' => $nasabah->email,
                'no_hp' => $nasabah->no_hp,
            ]
        );

        return redirect('/admin/data-nasabah')->with('success', "Nasabah baru berhasil didaftarkan dengan No. Rekening: {$no_rekening}!");
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
            'nama_siswa' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'jurusan' => 'required|string|max:100',
            'status' => 'nullable|in:aktif,alumni,nonaktif',
            'kategori' => 'nullable|in:siswa,guru,guru_karyawan,alumni',
            'email' => 'nullable|email|max:150',
            'no_hp' => 'nullable|string|max:25',
            'username' => 'nullable|string|max:100|unique:nasabah,username,'.$id,
            'password' => 'nullable|string|min:6',
        ]);

        $oldValues = [
            'nama_siswa' => $nasabah->nama_siswa,
            'kelas' => $nasabah->kelas,
            'jurusan' => $nasabah->jurusan,
            'status' => $nasabah->status,
            'kategori' => $nasabah->kategori,
            'email' => $nasabah->email,
            'no_hp' => $nasabah->no_hp,
            'username' => $nasabah->username,
        ];

        $nasabah->nama_siswa = $validated['nama_siswa'];
        $nasabah->kelas = $validated['kelas'];
        $nasabah->jurusan = $validated['jurusan'];
        if (isset($validated['status'])) {
            $nasabah->status = $validated['status'];
        }
        if (isset($validated['kategori'])) {
            $nasabah->kategori = $validated['kategori'];
        }
        if (array_key_exists('email', $validated)) {
            $nasabah->email = $validated['email'];
        }
        if (array_key_exists('no_hp', $validated)) {
            $nasabah->no_hp = $validated['no_hp'];
        }
        if (! empty($validated['username'])) {
            $nasabah->username = $validated['username'];
        }
        if (! empty($validated['password'])) {
            $nasabah->password = $validated['password'];
        }
        $nasabah->save();

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: $oldValues,
            newValues: [
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'jurusan' => $nasabah->jurusan,
                'status' => $nasabah->status,
                'kategori' => $nasabah->kategori,
                'email' => $nasabah->email,
                'no_hp' => $nasabah->no_hp,
                'username' => $nasabah->username,
            ]
        );

        return redirect('/admin/data-nasabah')->with('success', 'Data nasabah berhasil diperbarui!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $nasabah = Nasabah::findOrFail($id);

        AuditHelper::log(
            jenisAktivitas: 'HAPUS_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: [
                'no_rekening' => $nasabah->no_rekening,
                'nis' => $nasabah->nis,
                'nama_siswa' => $nasabah->nama_siswa,
                'saldo' => $nasabah->saldo,
                'status' => $nasabah->status,
            ],
            newValues: null
        );

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

            AuditHelper::log(
                jenisAktivitas: 'IMPORT_NASABAH',
                modul: 'Nasabah',
                objekId: null,
                oldValues: null,
                newValues: ['sukses' => $sukses, 'gagal' => $gagal]
            );

            return back()->with('success', "Import Selesai! {$sukses} data masuk dengan Nomor Rekening otomatis, {$gagal} data diabaikan (NIS kembar/kosong).");
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
