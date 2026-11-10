<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\SettingHelper;
use App\Helpers\TerbilangHelper;
use App\Models\Nasabah;
use App\Models\PinResetRequest;
use App\Models\Transaksi;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class NasabahController extends Controller
{
    /**
     * Tampilkan Daftar Nasabah dengan Pencarian & Multi-Filter Terpadu
     */
    public function index(Request $request): View
    {
        if ($request->status === 'terhapus') {
            $query = Nasabah::onlyTrashed();
        } else {
            $query = Nasabah::query();
        }

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
        if ($request->filled('status') && ! in_array($request->status, ['all', 'terhapus'], true)) {
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
        $totalTerhapus = Nasabah::onlyTrashed()->count();

        // Daftar Permohonan Reset PIN Nasabah yang Menunggu Persetujuan Admin
        $pinResetRequests = PinResetRequest::with('nasabah')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.data-nasabah', compact(
            'nasabah',
            'daftarKelas',
            'totalHasil',
            'totalSemua',
            'totalAktif',
            'totalNonaktif',
            'totalAlumni',
            'totalGuru',
            'totalTerhapus',
            'pinResetRequests'
        ));
    }

    /**
     * Halaman Detail Nasabah Komprehensif (Termasuk Alumni yang Terhapus agar Riwayat Tetap Terjaga)
     */
    public function show(int|string $id): View
    {
        $nasabah = Nasabah::withTrashed()->findOrFail($id);

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
        $no_rekening = $request->filled('no_rekening') ? $request->no_rekening : self::generateNoRekening($validated['jurusan'], $kategori, $validated['nis']);
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

        $alasan = request('alasan') ?: 'Pembaruan data nasabah oleh Administrator';

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: array_merge(['alasan' => $alasan], $oldValues),
            newValues: array_merge(['alasan' => $alasan], [
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'jurusan' => $nasabah->jurusan,
                'status' => $nasabah->status,
                'kategori' => $nasabah->kategori,
                'email' => $nasabah->email,
                'no_hp' => $nasabah->no_hp,
                'username' => $nasabah->username,
            ])
        );

        return redirect('/admin/data-nasabah')->with('success', 'Data nasabah berhasil diperbarui!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $nasabah = Nasabah::findOrFail($id);

        if ($nasabah->status === 'aktif') {
            return back()->with('error', 'Nasabah dengan status aktif tidak dapat dihapus/diarsipkan. Silakan nonaktifkan rekening terlebih dahulu sebelum menghapus.');
        }

        $alasan = request('alasan') ?: 'Pengarsipan / penghapusan data nasabah oleh Administrator';

        AuditHelper::log(
            jenisAktivitas: 'HAPUS_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: [
                'alasan' => $alasan,
                'no_rekening' => $nasabah->no_rekening,
                'nis' => $nasabah->nis,
                'nama_siswa' => $nasabah->nama_siswa,
                'saldo' => $nasabah->saldo,
                'status' => $nasabah->status,
            ],
            newValues: null
        );

        $nasabah->delete();

        return redirect('/admin/data-nasabah')->with('success', 'Data nasabah berhasil diarsipkan/dihapus dengan aman. Seluruh riwayat transaksi nasabah tetap tersimpan utuh di sistem!');
    }

    public function restore(int|string $id): RedirectResponse
    {
        $nasabah = Nasabah::onlyTrashed()->findOrFail($id);
        $nasabah->restore();

        AuditHelper::log(
            jenisAktivitas: 'PULIHKAN_NASABAH',
            modul: 'Nasabah',
            objekId: $nasabah->no_rekening,
            oldValues: ['deleted_at' => 'trashed'],
            newValues: ['status' => $nasabah->status]
        );

        return redirect('/admin/data-nasabah')->with('success', "Data nasabah {$nasabah->nama_siswa} berhasil dipulihkan!");
    }

    public static array $lastNumbers = [];

    /**
     * Algoritma Pembuatan Nomor Rekening Baru Otomatis
     * Mendukung Format JURUSAN-NIS (Contoh: RPL-17763, AKL-18001) serta Berbasis Urutan Standar
     *
     * @param  array<string, bool>  $knownAccounts  Cache memori no rekening untuk mencegah query berulang & gap lock
     */
    public static function generateNoRekening(string $jurusan, string $kategori = 'siswa', ?string $nis = null, array &$knownAccounts = []): string
    {
        $kode = ($kategori === 'guru_karyawan') ? 'GK' : TerbilangHelper::getKodeJurusan($jurusan);
        $setting = SettingHelper::getSettings();
        $format = $setting->format_no_rekening ?? 'JURUSAN-NIS';
        $ta = $setting->tahun_ajaran_aktif ?? '2026/2027';

        $checkExists = function (string $candidate) use (&$knownAccounts): bool {
            if (! empty($knownAccounts)) {
                return isset($knownAccounts[$candidate]);
            }

            return Nasabah::withTrashed()->where('no_rekening', $candidate)->exists();
        };

        // 1. Format JURUSAN-NIS (Contoh: RPL-17763, AKL-19126)
        if (($format === 'JURUSAN-NIS' || $format === 'KODE-NIS') && ! empty($nis)) {
            $candidate = "{$kode}-{$nis}";
            if (! $checkExists($candidate)) {
                $knownAccounts[$candidate] = true;

                return $candidate;
            }
            $suffix = 1;
            while ($checkExists("{$candidate}-{$suffix}")) {
                $suffix++;
            }

            $res = "{$candidate}-{$suffix}";
            $knownAccounts[$res] = true;

            return $res;
        }

        // 2. Format urutan standar
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

        if (isset(self::$lastNumbers[$prefixPola])) {
            $nextNum = self::$lastNumbers[$prefixPola] + 1;
        } else {
            // Ambil nasabah terakhir dengan format ini
            $lastRecord = Nasabah::withTrashed()
                ->where('no_rekening', 'LIKE', $prefixPola.'%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNum = 1;
            if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->no_rekening, $matches)) {
                $nextNum = (int) $matches[1] + 1;
            } else {
                // Kompatibilitas mundur dengan format lama (contoh: RPL-001)
                $lastOld = Nasabah::withTrashed()->where('no_rekening', 'LIKE', $kode.'-%')->orderBy('id', 'desc')->first();
                if ($lastOld && preg_match('/-(\d+)$/', $lastOld->no_rekening, $mOld)) {
                    $nextNum = (int) $mOld[1] + 1;
                } else {
                    $count = Nasabah::withTrashed()->where('jurusan', 'LIKE', "%{$jurusan}%")->count();
                    $nextNum = max(1, $count + 1);
                }
            }
        }

        // Pastikan tidak duplikat (menggunakan lookup in-memory jika tersedia untuk mencegah Gap Lock InnoDB)
        do {
            $candidate = $prefixPola.sprintf('%03d', $nextNum);
            $exists = $checkExists($candidate);
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        self::$lastNumbers[$prefixPola] = $nextNum;
        $knownAccounts[$candidate] = true;

        return $candidate;
    }

    // FUNGSI IMPORT DARI EXCEL (.xlsx, .xls) / CSV / DOKUMEN DAFTAR HADIR SISWA
    public function importCsv(Request $request): RedirectResponse
    {
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $request->validate([
            'file_csv' => 'required|file|max:20480',
            'status_default' => 'nullable|in:auto,nonaktif,aktif',
        ], [
            'file_csv.required' => 'Pilih file Excel (.xlsx, .xls) atau CSV (.csv, .txt) yang ingin diimpor.',
            'file_csv.max' => 'Ukuran file maksimal adalah 20MB.',
        ]);

        $file = $request->file('file_csv');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'txt', 'tsv'], true)) {
            return back()->with('error', 'Format file tidak didukung (.'.$extension.'). Mohon unggah dokumen Excel (.xlsx, .xls) atau CSV (.csv, .txt).');
        }

        $statusDefault = $request->input('status_default', 'auto');
        $filePath = $file->getRealPath();

        // 1. Ekstraksi baris data dari Excel (Multi-Sheet) atau File Teks/CSV
        $sheetGroups = [];

        try {
            if ($extension === 'xlsx') {
                // Gunakan parser XLSX mandiri (Zero-Dependency) yang bekerja 100% di cloud hosting
                $sheetGroups = self::parseXlsxNative($filePath);
            } elseif ($extension === 'xls') {
                // Untuk format XLS biner lama, gunakan IOFactory jika tersedia
                if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                    foreach ($spreadsheet->getAllSheets() as $sheet) {
                        $highestRow = $sheet->getHighestDataRow();
                        if ($highestRow < 2) {
                            continue;
                        }
                        $rows = $sheet->rangeToArray("A1:E{$highestRow}", null, true, true, false);
                        $sheetGroups[] = [
                            'sheet_title' => trim($sheet->getTitle()),
                            'rows' => $rows,
                        ];
                    }
                } else {
                    return back()->with('error', 'Format .xls (Excel lama) membutuhkan library khusus di server. Harap simpan file Anda sebagai format .xlsx (Excel modern) atau .csv lalu coba unggah kembali.');
                }
            } else {
                $handle = fopen($filePath, 'r');
                if (! $handle) {
                    return back()->with('error', 'Gagal membuka file dokumen yang diunggah.');
                }

                $firstLine = fgets($handle);
                $delimiter = ',';
                if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                    $delimiter = "\t";
                }
                rewind($handle);

                $csvRows = [];
                while (($row = fgetcsv($handle, 4000, $delimiter)) !== false) {
                    $csvRows[] = $row;
                }
                fclose($handle);

                $sheetGroups[] = [
                    'sheet_title' => 'CSV',
                    'rows' => $csvRows,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Import File Read Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Gagal membaca isi file: '.$e->getMessage());
        }

        if (empty($sheetGroups)) {
            return back()->with('error', 'Tidak ditemukan data di dalam dokumen yang diunggah.');
        }

        // 2. Parse & normalisasi seluruh baris data siswa SEBELUM membuka transaksi database
        $parsedStudents = [];
        $gagal = 0;

        foreach ($sheetGroups as $group) {
            $currentKelas = null;
            $currentJurusan = null;

            // Jika judul sheet berupa kelas langsung (misal: "X RPL 1" atau "XII LPS")
            $sheetTitle = $group['sheet_title'] ?? '';
            if (preg_match('/^(?:X|XI|XII)\s+[A-Z0-9\s]+$/i', $sheetTitle)) {
                $currentKelas = $sheetTitle;
                $currentJurusan = TerbilangHelper::getNamaJurusan($currentKelas);
            }

            foreach ($group['rows'] as $row) {
                // Bersihkan setiap kolom dari whitespace
                $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

                // Lewati baris kosong
                if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                $rowJoined = implode(' ', array_filter($row));

                // 1. Deteksi Baris Header Kelas (Contoh: "KELAS : X RPL 1", "KELAS : XII AK 2", "KELAS : X BD")
                if (preg_match('/KELAS\s*:\s*([^,\r\n;]+)/i', $rowJoined, $m)) {
                    $currentKelas = trim(str_replace(['"', "'"], '', $m[1]));
                    $currentJurusan = TerbilangHelper::getNamaJurusan($currentKelas);
                    continue;
                }

                // Lewati baris metadata/judul dokumen absensi sekolah
                if (preg_match('/(DAFTAR HADIR|TAHUN PELAJARAN|MATA PELAJARAN|GURU MATA|JUMLAH SISWA|PERTEMUAN KE|Guru Mata Pelajaran|Bantul,|NIP\.)/i', $rowJoined)) {
                    continue;
                }

                $nis = null;
                $nama = null;
                $kelas = null;
                $jurusan = null;
                $statusKolom = '';

                // 2. Format Dokumen Daftar Hadir / Absensi Siswa SMKN 1 Bantul:
                // Col 0: No Urut (1, 2, 3..), Col 1: NIS (e.g. 18910), Col 2: Nama Siswa, Col 3: L/P
                if (
                    isset($row[0], $row[1], $row[2]) &&
                    is_numeric($row[0]) &&
                    is_numeric($row[1]) &&
                    strlen((string) $row[1]) >= 4 &&
                    ! empty($row[2]) &&
                    ! is_numeric($row[2])
                ) {
                    $nis = (string) $row[1];
                    $nama = trim(rtrim(trim((string) $row[2]), " *\t\n\r\0\x0B"));
                    $nama = rtrim($nama, '*');
                    $nama = trim($nama);
                    $kelas = $currentKelas ?: '-';
                    $jurusan = $currentJurusan ?: TerbilangHelper::getNamaJurusan($kelas);
                }
                // Format Absensi dengan kolom bergeser (Col 1: No Urut, Col 2: NIS, Col 3: Nama)
                elseif (
                    isset($row[1], $row[2], $row[3]) &&
                    is_numeric($row[1]) &&
                    is_numeric($row[2]) &&
                    strlen((string) $row[2]) >= 4 &&
                    ! empty($row[3]) &&
                    ! is_numeric($row[3])
                ) {
                    $nis = (string) $row[2];
                    $nama = trim(rtrim(trim((string) $row[3]), " *\t\n\r\0\x0B"));
                    $nama = rtrim($nama, '*');
                    $nama = trim($nama);
                    $kelas = $currentKelas ?: '-';
                    $jurusan = $currentJurusan ?: TerbilangHelper::getNamaJurusan($kelas);
                }
                // 3. Format CSV / Excel Standar (Col 0: NIS, Col 1: Nama, Col 2: Kelas, Col 3: Jurusan, Col 4: Status)
                elseif (
                    isset($row[0], $row[1]) &&
                    ! empty($row[0]) &&
                    ! empty($row[1]) &&
                    ! is_numeric($row[1])
                ) {
                    // Cek jika ini baris header template ("NIS", "Nama Lengkap", "Urt", dll)
                    if (preg_match('/^(nis|nomor|no|urt)$/i', (string) $row[0])) {
                        continue;
                    }

                    if (! is_numeric($row[0]) || strlen((string) $row[0]) < 4) {
                        continue;
                    }

                    $nis = (string) $row[0];
                    $nama = trim(rtrim(trim((string) $row[1]), " *\t\n\r\0\x0B"));
                    $nama = rtrim($nama, '*');
                    $nama = trim($nama);
                    $kelas = isset($row[2]) && $row[2] !== '' ? $row[2] : ($currentKelas ?: '-');
                    $jurusan = isset($row[3]) && $row[3] !== '' ? $row[3] : ($currentJurusan ?: TerbilangHelper::getNamaJurusan($kelas));
                    $statusKolom = isset($row[4]) ? strtolower(trim((string) $row[4])) : '';
                } else {
                    // Baris tidak dikenali / kosong
                    continue;
                }

                if (empty($nis) || empty($nama)) {
                    $gagal++;
                    continue;
                }

                // Tentukan status akun nasabah
                if (in_array($statusKolom, ['aktif', 'nonaktif', 'alumni'], true)) {
                    $statusNasabah = $statusKolom;
                } elseif ($statusDefault === 'nonaktif') {
                    $statusNasabah = 'nonaktif';
                } elseif ($statusDefault === 'aktif') {
                    $statusNasabah = 'aktif';
                } else {
                    // Otomatis: Kelas 10 (X) = Belum Aktif (nonaktif), Kelas 11 (XI) & 12 (XII) = Aktif
                    if (preg_match('/\b(?:X|10)\b/i', $kelas) && ! preg_match('/\b(?:XI|XII|11|12)\b/i', $kelas)) {
                        $statusNasabah = 'nonaktif';
                    } else {
                        $statusNasabah = 'aktif';
                    }
                }

                $parsedStudents[] = [
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas' => $kelas,
                    'jurusan' => $jurusan,
                    'status' => $statusNasabah,
                ];
            }
        }

        if (empty($parsedStudents)) {
            return back()->with('error', 'Tidak ada baris data siswa yang valid untuk diimpor. Pastikan dokumen Anda memiliki kolom NIS dan Nama yang benar.');
        }

        // 3. Backend Atomic Lock: Cegah eksekusi ganda jika tombol diklik berkali-kali atau dibuka di tab berbeda
        $lock = null;
        $lockAcquired = false;
        try {
            $lock = Cache::lock('import_nasabah_process_lock', 120);
            $lockAcquired = $lock->get();
        } catch (\Throwable) {
            $lock = null;
            $lockAcquired = true; // Fallback toleran jika driver cache tidak mendukung atomic lock
        }

        if (! $lockAcquired) {
            return back()->with('error', 'Proses impor data nasabah sedang berjalan di server. Mohon tunggu sejenak hingga proses selesai sebelum mengimpor kembali.');
        }

        try {
            // 1. Ubah Transaction Isolation Level sesi ke READ COMMITTED untuk menghilangkan Gap-Lock InnoDB
            try {
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
            } catch (\Throwable) {}

            // 2. Pre-hash password default '123456' SEKALI SAJA di luar loop (menghemat 99% waktu eksekusi CPU Bcrypt)
            $defaultPasswordHashed = Hash::make('123456');

            // 3. Pre-fetch seluruh Nomor Rekening yang sudah ada ke memori (O(1) hash lookup tanpa SQL di dalam loop)
            $knownAccounts = Nasabah::withTrashed()
                ->pluck('no_rekening')
                ->flip()
                ->toArray();

            // 4. Reset nomor urut terakhir di memori
            self::$lastNumbers = [];
            $sukses = 0;
            $diperbarui = 0;

            // 5. Proses baris per baris secara atomik independen dengan mekanisme retry otomatis per baris
            foreach ($parsedStudents as $student) {
                $nis = $student['nis'];
                $nama = $student['nama'];
                $kelas = $student['kelas'];
                $jurusan = $student['jurusan'];
                $statusNasabah = $student['status'];

                try {
                    retry(3, function () use (
                        $nis,
                        $nama,
                        $kelas,
                        $jurusan,
                        $statusNasabah,
                        &$sukses,
                        &$diperbarui,
                        &$knownAccounts,
                        $defaultPasswordHashed
                    ) {
                        DB::transaction(function () use (
                            $nis,
                            $nama,
                            $kelas,
                            $jurusan,
                            $statusNasabah,
                            &$sukses,
                            &$diperbarui,
                            &$knownAccounts,
                            $defaultPasswordHashed
                        ) {
                            $existing = Nasabah::withTrashed()->where('nis', $nis)->first();

                            if ($existing) {
                                if ($existing->trashed()) {
                                    $existing->restore();
                                }
                                $existing->update([
                                    'nama_siswa' => $nama,
                                    'kelas' => $kelas,
                                    'jurusan' => $jurusan,
                                ]);
                                $diperbarui++;
                            } else {
                                $no_rekening = self::generateNoRekening($jurusan, 'siswa', $nis, $knownAccounts);

                                Nasabah::create([
                                    'no_rekening' => $no_rekening,
                                    'nis' => $nis,
                                    'nama_siswa' => $nama,
                                    'kelas' => $kelas,
                                    'jurusan' => $jurusan,
                                    'username' => $nis,
                                    'password' => $defaultPasswordHashed,
                                    'pin_keamanan' => '123456',
                                    'saldo' => 0,
                                    'status' => $statusNasabah,
                                    'kategori' => 'siswa',
                                ]);

                                $knownAccounts[$no_rekening] = true;
                                $sukses++;
                            }
                        });
                    }, 100);
                } catch (\Throwable $rowError) {
                    $gagal++;
                    Log::warning("Gagal impor data siswa baris NIS {$nis} ({$nama}): ".$rowError->getMessage());
                }
            }

            AuditHelper::log(
                jenisAktivitas: 'IMPORT_NASABAH',
                modul: 'Nasabah',
                objekId: null,
                oldValues: null,
                newValues: [
                    'sukses_baru' => $sukses,
                    'diperbarui' => $diperbarui,
                    'gagal' => $gagal,
                    'status_default' => $statusDefault,
                ]
            );

            $pesan = 'Import Selesai! ';
            if ($sukses > 0) {
                $pesan .= "{$sukses} siswa baru berhasil didaftarkan dengan Nomor Rekening otomatis. ";
            }
            if ($diperbarui > 0) {
                $pesan .= "{$diperbarui} data siswa berhasil disinkronkan/diperbarui. ";
            }
            if ($gagal > 0) {
                $pesan .= "{$gagal} baris dilewati (format tidak sesuai).";
            }

            return back()->with('success', trim($pesan));
        } catch (\Throwable $e) {
            Log::error('Import File Processing Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Gagal impor data: '.$e->getMessage());
        } finally {
            try {
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            } catch (\Throwable) {}

            if ($lock && $lockAcquired) {
                try {
                    $lock->release();
                } catch (\Throwable) {}
            }
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

        $columns = ['NIS', 'Nama Lengkap', 'Kelas (Contoh: X RPL 1 / XII RPL 1)', 'Jurusan (Contoh: Rekayasa Perangkat Lunak)', 'Status Opsional (aktif/nonaktif)'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');
            fputcsv($file, ['10001', 'Contoh Nama Siswa Satu', 'X RPL 1', 'Rekayasa Perangkat Lunak', 'nonaktif'], ';');
            fputcsv($file, ['10002', 'Contoh Nama Siswa Dua', 'XI RPL 1', 'Rekayasa Perangkat Lunak', 'aktif'], ';');
            fputcsv($file, ['10003', 'Contoh Nama Siswa Tiga', 'XII RPL 2', 'Rekayasa Perangkat Lunak', 'aktif'], ';');
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
        $pinResetRequest = null;

        if ($nasabah) {
            $transaksi = Transaksi::where('nasabah_id', $nasabah->id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $pinResetRequest = PinResetRequest::where('nasabah_id', $nasabah->id)
                ->latest()
                ->first();
        }

        return view('nasabah.dashboard', compact('nasabah', 'transaksi', 'pinResetRequest'));
    }

    /**
     * Parser XLSX mandiri (Zero-Dependency) menggunakan ekstensi bawaan PHP (ZipArchive & SimpleXML).
     * Solusi ini 100% aman dan bekerja di semua lingkungan hosting/cPanel tanpa memerlukan library Composer tambahan.
     *
     * @return array<int, array{sheet_title: string, rows: array<int, array<int, string>>}>
     */
    protected static function parseXlsxNative(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Gagal membuka arsip file format Excel (.xlsx). Pastikan file tidak rusak.');
        }

        // 1. Ambil tabel shared strings (kamus string bersama Excel)
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml') ?: $zip->getFromName('xl/SharedStrings.xml');
        if ($ssXml) {
            $xml = @simplexml_load_string($ssXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca relasi workbook untuk memetakan nama sheet ke file XML-nya
        $sheetTargets = [];
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($relsXml) {
            $rels = @simplexml_load_string($relsXml);
            if ($rels) {
                foreach ($rels->Relationship as $rel) {
                    $sheetTargets[(string) $rel['Id']] = (string) $rel['Target'];
                }
            }
        }

        // 3. Baca daftar sheets di workbook
        $sheets = [];
        $wbXml = $zip->getFromName('xl/workbook.xml');
        if ($wbXml) {
            $wb = @simplexml_load_string($wbXml);
            if ($wb && isset($wb->sheets->sheet)) {
                foreach ($wb->sheets->sheet as $s) {
                    $name = (string) $s['name'];
                    $rId = (string) $s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                    $target = $sheetTargets[$rId] ?? '';
                    if ($target) {
                        $entryPath = ltrim(str_replace('\\', '/', $target), '/');
                        if (! str_starts_with($entryPath, 'xl/')) {
                            $entryPath = 'xl/'.$entryPath;
                        }
                        $sheets[] = [
                            'name' => $name,
                            'path' => $entryPath,
                        ];
                    }
                }
            }
        }

        // Fallback jika tidak ada deklarasi rels
        if (empty($sheets)) {
            for ($i = 1; $i <= 50; $i++) {
                $p = "xl/worksheets/sheet{$i}.xml";
                if ($zip->locateName($p) !== false) {
                    $sheets[] = ['name' => "Sheet{$i}", 'path' => $p];
                }
            }
        }

        // Helper konversi abjad kolom Excel ke indeks 0-based: A => 0, B => 1, ...
        $colLetterToIndex = function (string $letters): int {
            $letters = strtoupper($letters);
            $len = strlen($letters);
            $num = 0;
            for ($i = 0; $i < $len; $i++) {
                $num = $num * 26 + (ord($letters[$i]) - 64);
            }

            return $num - 1;
        };

        $sheetGroups = [];

        foreach ($sheets as $sh) {
            $sheetXml = $zip->getFromName($sh['path']);
            if (! $sheetXml) {
                continue;
            }

            $xml = @simplexml_load_string($sheetXml);
            if (! $xml || ! isset($xml->sheetData->row)) {
                continue;
            }

            $sheetRows = [];

            foreach ($xml->sheetData->row as $row) {
                $cells = [];
                $maxIdx = -1;

                foreach ($row->c as $c) {
                    $ref = (string) $c['r']; // Contoh: "B12"
                    if (! preg_match('/^([A-Z]+)(\d+)$/', $ref, $m)) {
                        continue;
                    }
                    $colIdx = $colLetterToIndex($m[1]);
                    // Kita hanya butuh data nasabah hingga kolom E atau lebih sedikit, batasi sampai 25 kolom
                    if ($colIdx > 25) {
                        continue;
                    }

                    $type = (string) $c['t'];
                    $val = '';

                    if ($type === 's') {
                        $idx = (int) $c->v;
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                        $val = (string) $c->is->t;
                    } else {
                        $val = isset($c->v) ? (string) $c->v : '';
                    }

                    $cells[$colIdx] = trim($val);
                    if ($colIdx > $maxIdx) {
                        $maxIdx = $colIdx;
                    }
                }

                if ($maxIdx >= 0) {
                    $orderedRow = [];
                    for ($k = 0; $k <= $maxIdx; $k++) {
                        $orderedRow[] = $cells[$k] ?? '';
                    }
                    $sheetRows[] = $orderedRow;
                }
            }

            if (! empty($sheetRows)) {
                $sheetGroups[] = [
                    'sheet_title' => $sh['name'],
                    'rows' => $sheetRows,
                ];
            }
        }

        $zip->close();

        return $sheetGroups;
    }
}
