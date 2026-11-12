<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\SettingHelper;
use App\Helpers\TerbilangHelper;
use App\Models\Nasabah;
use App\Models\PinResetRequest;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $totalSaldoSemua = (int) Nasabah::sum('saldo');

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
            'totalSaldoSemua',
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
        $password = $validated['password'] ?? 'password';

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

        $oldStatus = $nasabah->status;
        $nasabah->nama_siswa = $validated['nama_siswa'];
        $nasabah->kelas = $validated['kelas'];
        $nasabah->jurusan = $validated['jurusan'];
        if (isset($validated['status'])) {
            $nasabah->status = $validated['status'];
            // Jika akun diaktifkan dari nonaktif, otomatis generate kata sandi default "password" jika tidak diisi
            if ($validated['status'] === 'aktif' && $oldStatus !== 'aktif' && empty($validated['password'])) {
                $nasabah->password = 'password';
            }
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

    /**
     * Nonaktifkan seluruh rekening nasabah berstatus aktif secara massal (khusus Administrator).
     */
    public function deactivateAll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alasan' => 'nullable|string|max:255',
        ]);

        $alasan = ! empty($validated['alasan'])
            ? trim($validated['alasan'])
            : 'Penonaktifan seluruh rekening nasabah aktif secara massal oleh Administrator';

        $jumlahAktif = Nasabah::where('status', 'aktif')->count();
        if ($jumlahAktif === 0) {
            return back()->with('error', 'Tidak ada rekening nasabah berstatus aktif yang perlu dinonaktifkan.');
        }

        $totalSaldoTerdampak = (int) Nasabah::where('status', 'aktif')->sum('saldo');

        DB::transaction(function () use ($jumlahAktif, $totalSaldoTerdampak, $alasan): void {
            Nasabah::where('status', 'aktif')->update([
                'status' => 'nonaktif',
                'updated_at' => now(),
            ]);

            AuditHelper::log(
                jenisAktivitas: 'NONAKTIFKAN_SEMUA_NASABAH',
                modul: 'Nasabah',
                objekId: 'SEMUA',
                oldValues: [
                    'status' => 'aktif',
                    'jumlah_aktif' => $jumlahAktif,
                    'total_saldo_terdampak' => $totalSaldoTerdampak,
                ],
                newValues: [
                    'status' => 'nonaktif',
                    'jumlah_dinonaktifkan' => $jumlahAktif,
                    'alasan' => $alasan,
                ]
            );
        });

        return redirect('/admin/data-nasabah')->with('success', number_format($jumlahAktif, 0, ',', '.').' rekening nasabah aktif berhasil dinonaktifkan.');
    }

    /**
     * Hapus (soft-delete) seluruh nasabah nonaktif/alumni secara massal (khusus Administrator).
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'konfirmasi' => 'required|string|in:HAPUS SEMUA',
            'alasan' => 'nullable|string|max:255',
        ], [
            'konfirmasi.required' => 'Konfirmasi wajib diisi dengan teks "HAPUS SEMUA".',
            'konfirmasi.in' => 'Teks konfirmasi tidak sesuai. Ketik persis "HAPUS SEMUA" untuk melanjutkan penghapusan massal.',
        ]);

        $jumlahAktif = Nasabah::where('status', 'aktif')->count();
        if ($jumlahAktif > 0) {
            return back()->with('error', 'Masih terdapat '.number_format($jumlahAktif, 0, ',', '.').' nasabah berstatus aktif. Silakan klik "Nonaktifkan Semua Nasabah" terlebih dahulu sebelum menghapus seluruh data nasabah.');
        }

        $jumlahTarget = Nasabah::where('status', '!=', 'aktif')->count();
        if ($jumlahTarget === 0) {
            return back()->with('error', 'Tidak ada data nasabah yang tersedia untuk dihapus atau diarsipkan.');
        }

        $alasan = ! empty($validated['alasan'])
            ? trim($validated['alasan'])
            : 'Penghapusan / pengarsipan seluruh data nasabah secara massal oleh Administrator';

        $jumlahNonaktif = Nasabah::where('status', 'nonaktif')->count();
        $jumlahAlumni = Nasabah::where('status', 'alumni')->count();
        $totalSaldoTerdampak = (int) Nasabah::where('status', '!=', 'aktif')->sum('saldo');

        DB::transaction(function () use ($jumlahTarget, $jumlahNonaktif, $jumlahAlumni, $totalSaldoTerdampak, $alasan): void {
            AuditHelper::log(
                jenisAktivitas: 'HAPUS_SEMUA_NASABAH',
                modul: 'Nasabah',
                objekId: 'SEMUA',
                oldValues: [
                    'alasan' => $alasan,
                    'jumlah_terhapus' => $jumlahTarget,
                    'rincian_nonaktif' => $jumlahNonaktif,
                    'rincian_alumni' => $jumlahAlumni,
                    'total_saldo_terarsip' => $totalSaldoTerdampak,
                ],
                newValues: null
            );

            // Gunakan Soft Delete massal agar foreign key transaksi (onDelete cascade) tetap aman & riwayat utuh.
            Nasabah::where('status', '!=', 'aktif')->delete();
        });

        return redirect('/admin/data-nasabah')->with('success', number_format($jumlahTarget, 0, ',', '.').' data nasabah berhasil dihapus/diarsipkan dengan aman. Seluruh riwayat transaksi tetap tersimpan utuh di sistem!');
    }

    public static array $lastNumbers = [];

    /**
     * Algoritma Pembuatan Nomor Rekening Baru Otomatis
     * Mendukung Format JURUSAN-NIS (Contoh: RPL-17763, AKL-18001) serta Berbasis Urutan Standar
     */
    /**
     * Konversi kode huruf kolom Excel (A, B, ..., Z, AA, AB) ke index integer 0-based.
     */
    private static function colLettersToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }

        return $index - 1;
    }

    /**
     * Parser native file Excel .xlsx berbasis ZipArchive dan XMLReader streaming.
     * Sangat hemat memori (<2MB) dan tidak memerlukan library eksternal (PhpSpreadsheet).
     *
     * @return array<int, array<int, string>>
     */
    private static function parseXlsxNative(string $filePath): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Gagal membuka file arsip Excel (.xlsx). Pastikan file tidak rusak.');
        }

        // 1. Ekstraksi Shared Strings jika ada (penyimpanan teks berulang Excel)
        $sharedStrings = [];
        $sharedXmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXmlContent !== false) {
            $xml = new \XMLReader;
            $xml->xml($sharedXmlContent);
            while ($xml->read()) {
                if ($xml->nodeType === \XMLReader::ELEMENT && $xml->name === 'si') {
                    $node = new \SimpleXMLElement($xml->readOuterXml());
                    $text = '';
                    if (isset($node->t)) {
                        $text = (string) $node->t;
                    } else {
                        $tNodes = $node->xpath('.//*[local-name()="t"]') ?: [];
                        foreach ($tNodes as $t) {
                            $text .= (string) $t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
            $xml->close();
        }

        // 2. Petakan nama sheet dari workbook.xml dan relasi
        $sheetMap = [];
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        $relMap = [];
        if ($relsXml !== false) {
            $rels = @simplexml_load_string($relsXml);
            if ($rels) {
                foreach ($rels->Relationship as $rel) {
                    $id = (string) $rel['Id'];
                    $target = (string) $rel['Target'];
                    if (str_starts_with($target, '/')) {
                        $target = ltrim($target, '/');
                    } elseif (! str_starts_with($target, 'xl/')) {
                        $target = 'xl/'.$target;
                    }
                    $relMap[$id] = $target;
                }
            }
        }

        if ($workbookXml !== false) {
            $wb = @simplexml_load_string($workbookXml);
            if ($wb && isset($wb->sheets->sheet)) {
                foreach ($wb->sheets->sheet as $s) {
                    $name = (string) $s['name'];
                    $rId = (string) ($s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'] ?? '');
                    $targetFile = $relMap[$rId] ?? '';
                    if ($targetFile !== '') {
                        $sheetMap[$targetFile] = $name;
                    }
                }
            }
        }

        if (empty($sheetMap)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $filename)) {
                    $sheetMap[$filename] = pathinfo($filename, PATHINFO_FILENAME);
                }
            }
        }

        $allRows = [];

        // 3. Baca tiap worksheet secara streaming
        foreach ($sheetMap as $sheetPath => $sheetName) {
            $sheetContent = $zip->getFromName($sheetPath);
            if ($sheetContent === false) {
                continue;
            }

            // Jika nama sheet mencerminkan tingkatan kelas (misal: "X RPL 1", "XI AKL 2"), sisipkan baris penanda kelas
            if (preg_match('/\b(X|XI|XII|10|11|12)\b/i', $sheetName) && ! preg_match('/^sheet\d+$/i', $sheetName)) {
                $allRows[] = ['KELAS : '.$sheetName];
            }

            $xml = new \XMLReader;
            $xml->xml($sheetContent);

            while ($xml->read()) {
                if ($xml->nodeType === \XMLReader::ELEMENT && $xml->name === 'row') {
                    $rowXml = $xml->readOuterXml();
                    $rowElement = @simplexml_load_string($rowXml);
                    if (! $rowElement) {
                        continue;
                    }

                    $rowCells = [];
                    $maxCol = -1;

                    foreach ($rowElement->c as $cell) {
                        $r = (string) ($cell['r'] ?? '');
                        preg_match('/^([A-Z]+)(\d+)/', strtoupper($r), $matches);
                        $colLetters = $matches[1] ?? 'A';
                        $colIndex = self::colLettersToIndex($colLetters);

                        $type = (string) ($cell['t'] ?? '');
                        $val = '';

                        if ($type === 's') {
                            $sIndex = (int) ($cell->v ?? 0);
                            $val = $sharedStrings[$sIndex] ?? '';
                        } elseif ($type === 'inlineStr') {
                            $val = isset($cell->is->t) ? (string) $cell->is->t : (string) (($cell->xpath('.//*[local-name()="t"]')[0] ?? ''));
                        } elseif (isset($cell->v)) {
                            $val = (string) $cell->v;
                        }

                        $rowCells[$colIndex] = $val;
                        if ($colIndex > $maxCol) {
                            $maxCol = $colIndex;
                        }
                    }

                    if ($maxCol >= 0) {
                        $fullRow = [];
                        for ($c = 0; $c <= $maxCol; $c++) {
                            $fullRow[$c] = $rowCells[$c] ?? '';
                        }
                        $allRows[] = $fullRow;
                    }
                }
            }
            $xml->close();
        }

        $zip->close();

        return $allRows;
    }

    /**
     * Generator template file Excel (.xlsx) murni dengan ZipArchive tanpa ketergantungan library eksternal.
     *
     * @param  array<int, array<int, string>>  $rows
     */
    private static function generateMinimalXlsx(array $rows, string $outputPath): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file template Excel (.xlsx).');
        }

        $sharedStrings = [];
        $stringMap = [];
        foreach ($rows as $r) {
            foreach ($r as $val) {
                $s = (string) $val;
                if (! isset($stringMap[$s])) {
                    $stringMap[$s] = count($sharedStrings);
                    $sharedStrings[] = $s;
                }
            }
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            .'</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            .'</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/workbook.xml
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .'<sheet name="Template Import Nasabah" sheetId="1" r:id="rId1"/>'
            .'</sheets>'
            .'</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // xl/sharedStrings.xml
        $count = count($sharedStrings);
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.$count.'" uniqueCount="'.$count.'">';
        foreach ($sharedStrings as $str) {
            $ssXml .= '<si><t>'.htmlspecialchars($str, ENT_XML1, 'UTF-8').'</t></si>';
        }
        $ssXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);

        // xl/worksheets/sheet1.xml
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n";
        $sheetXml .= '  <sheetData>'."\n";

        $rNum = 1;
        foreach ($rows as $row) {
            $sheetXml .= '    <row r="'.$rNum.'">'."\n";
            $cNum = 0;
            foreach ($row as $val) {
                $colLetter = $colLetters[$cNum] ?? 'A';
                $cRef = $colLetter.$rNum;
                $sIdx = $stringMap[(string) $val];
                $sheetXml .= '      <c r="'.$cRef.'" t="s"><v>'.$sIdx.'</v></c>'."\n";
                $cNum++;
            }
            $sheetXml .= '    </row>'."\n";
            $rNum++;
        }
        $sheetXml .= '  </sheetData>'."\n";
        $sheetXml .= '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
    }

    public static function generateNoRekening(string $jurusan, string $kategori = 'siswa', ?string $nis = null): string
    {
        $kode = ($kategori === 'guru_karyawan') ? 'GK' : TerbilangHelper::getKodeJurusan($jurusan);
        $setting = SettingHelper::getSettings();
        $format = $setting->format_no_rekening ?? 'JURUSAN-NIS';
        $ta = $setting->tahun_ajaran_aktif ?? '2026/2027';

        // 1. Format JURUSAN-NIS (Contoh: RPL-17763, AKL-19126)
        if (($format === 'JURUSAN-NIS' || $format === 'KODE-NIS') && ! empty($nis)) {
            $candidate = "{$kode}-{$nis}";
            if (! Nasabah::withTrashed()->where('no_rekening', $candidate)->exists()) {
                return $candidate;
            }
            $suffix = 1;
            while (Nasabah::withTrashed()->where('no_rekening', "{$candidate}-{$suffix}")->exists()) {
                $suffix++;
            }

            return "{$candidate}-{$suffix}";
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

        // Pastikan tidak duplikat
        do {
            $candidate = $prefixPola.sprintf('%03d', $nextNum);
            $exists = Nasabah::withTrashed()->where('no_rekening', $candidate)->exists();
            if ($exists) {
                $nextNum++;
            }
        } while ($exists);

        self::$lastNumbers[$prefixPola] = $nextNum;

        return $candidate;
    }

    // FUNGSI IMPORT DARI EXCEL (.xlsx, .xls) / CSV / DOKUMEN DAFTAR HADIR SISWA
    public function importCsv(Request $request): RedirectResponse
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $request->validate([
            'file_csv' => 'required|file|max:10240',
            'status_default' => 'nullable|in:auto,nonaktif,aktif',
        ], [
            'file_csv.required' => 'Pilih file Excel (.xlsx, .xls) atau CSV (.csv, .txt) yang ingin diimpor.',
            'file_csv.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $file = $request->file('file_csv');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            return back()->with('error', 'Format file tidak didukung (.'.$extension.'). Mohon unggah file format Excel (.xlsx, .xls) atau CSV (.csv, .txt).');
        }

        $statusDefault = $request->input('status_default', 'auto');
        $filePath = $file->getPathname();
        $rows = [];

        try {
            if ($extension === 'xlsx') {
                // Gunakan parser native streaming yang sangat hemat memori (<2MB) dan tanpa ketergantungan library eksternal (PhpSpreadsheet)
                $rows = self::parseXlsxNative($filePath);
            } elseif ($extension === 'xls') {
                // Untuk file format binary lama (.xls), gunakan IOFactory jika library tersedia di server
                if (class_exists(IOFactory::class)) {
                    $reader = IOFactory::createReaderForFile($filePath);
                    if (method_exists($reader, 'setReadDataOnly')) {
                        $reader->setReadDataOnly(true);
                    }
                    if (method_exists($reader, 'setReadEmptyCells')) {
                        $reader->setReadEmptyCells(false);
                    }

                    $spreadsheet = $reader->load($filePath);

                    foreach ($spreadsheet->getAllSheets() as $sheet) {
                        $highestRow = $sheet->getHighestDataRow();
                        if ($highestRow < 1) {
                            continue;
                        }

                        $highestCol = $sheet->getHighestDataColumn();
                        $sheetTitle = trim($sheet->getTitle());

                        if (preg_match('/\b(X|XI|XII|10|11|12)\b/i', $sheetTitle) && ! preg_match('/^sheet\d+$/i', $sheetTitle)) {
                            $rows[] = ['KELAS : '.$sheetTitle];
                        }

                        $sheetRows = $sheet->rangeToArray('A1:'.$highestCol.$highestRow, null, true, false, false);
                        foreach ($sheetRows as $r) {
                            $rows[] = $r;
                        }
                    }

                    $spreadsheet->disconnectWorksheets();
                    unset($spreadsheet, $reader);
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                } else {
                    return back()->with('error', 'Format .xls (Excel 97-2003 lama) tidak didukung langsung oleh server cPanel tanpa terminal. Mohon simpan atau ekspor file Anda ke format .xlsx (Excel modern) atau .csv lalu unggah kembali.');
                }
            } else {
                $handle = fopen($filePath, 'r');
                if (! $handle) {
                    return back()->with('error', 'Gagal membuka file dokumen yang diunggah.');
                }

                // Auto-detect delimiter (; or , or \t)
                $firstLine = fgets($handle);
                $delimiter = ',';
                if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                    $delimiter = "\t";
                }
                rewind($handle);

                while (($row = fgetcsv($handle, 2000, $delimiter, '"', '\\')) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca isi file dokumen: '.$e->getMessage());
        }

        $sukses = 0;
        $diperbarui = 0;
        $gagal = 0;
        $currentKelas = null;
        $currentJurusan = null;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Bersihkan setiap kolom dari whitespace dan normalisasi angka (float/int ke string)
                $row = array_map(function ($v) {
                    if ($v === null || $v === '') {
                        return '';
                    }
                    if (is_float($v) && floor($v) == $v) {
                        return (string) (int) $v;
                    }

                    return trim((string) $v);
                }, (array) $row);

                // Buang kolom kosong di awal baris jika ada (misal tabel dimulai di kolom B)
                while (! empty($row) && reset($row) === '') {
                    array_shift($row);
                }

                // Lewati baris kosong
                if (empty(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                $rowJoined = implode(' ', array_filter($row));

                // 1. Deteksi Baris Header Kelas (Contoh: "KELAS : X RPL 1", "KELAS : XII AK 2", "KELAS : X BD")
                if (preg_match('/KELAS\s*:\s*([^,\r\n;]+)/i', $rowJoined, $m)) {
                    $currentKelas = trim($m[1]);
                    $currentJurusan = TerbilangHelper::getNamaJurusan($currentKelas);

                    continue;
                }

                // Lewati baris metadata/judul dokumen absensi
                if (preg_match('/(DAFTAR HADIR|TAHUN PELAJARAN|MATA PELAJARAN|GURU MATA|JUMLAH SISWA|PERTEMUAN KE|Guru Mata Pelajaran|Bantul,|NIP\.)/i', $rowJoined)) {
                    continue;
                }

                $nis = null;
                $nama = null;
                $kelas = null;
                $jurusan = null;
                $statusKolom = '';

                // 2. Format File Absensi Sekolah SMKN 1 Bantul:
                // Col 0: No Urut (1, 2, 3..), Col 1: NIS (e.g. 18910), Col 2: Nama Siswa, Col 3: L/P
                if (
                    isset($row[0], $row[1], $row[2]) &&
                    is_numeric($row[0]) &&
                    is_numeric($row[1]) &&
                    strlen($row[1]) >= 4 &&
                    ! empty($row[2])
                ) {
                    $nis = (string) $row[1];
                    $nama = rtrim($row[2], " *\t\n\r\0\x0B");
                    $kelas = $currentKelas ?: '-';
                    $jurusan = $currentJurusan ?: TerbilangHelper::getNamaJurusan($kelas);
                }
                // 3. Format CSV / Excel Standar (Col 0: NIS, Col 1: Nama, Col 2: Kelas, Col 3: Jurusan, Col 4: Status)
                elseif (
                    isset($row[0], $row[1]) &&
                    ! empty($row[0]) &&
                    ! empty($row[1])
                ) {
                    // Cek jika ini baris header template ("NIS", "Nama Lengkap", "Urt", dll)
                    if (preg_match('/^(nis|nomor|no|urt|no\s*urut|id)$/i', $row[0])) {
                        continue;
                    }

                    $nis = (string) $row[0];
                    $nama = rtrim($row[1], " *\t\n\r\0\x0B");
                    $kelas = isset($row[2]) && $row[2] !== '' ? $row[2] : ($currentKelas ?: '-');
                    $jurusan = isset($row[3]) && $row[3] !== '' ? $row[3] : ($currentJurusan ?: TerbilangHelper::getNamaJurusan($kelas));
                    $statusKolom = isset($row[4]) ? strtolower(trim($row[4])) : '';
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

                $exists = Nasabah::withTrashed()->where('nis', $nis)->first();
                if ($exists) {
                    if ($exists->trashed()) {
                        $exists->restore();
                    }
                    $exists->update([
                        'nama_siswa' => $nama,
                        'kelas' => $kelas,
                        'jurusan' => $jurusan,
                    ]);
                    $diperbarui++;
                } else {
                    $no_rekening = self::generateNoRekening($jurusan, 'siswa', $nis);

                    Nasabah::create([
                        'no_rekening' => $no_rekening,
                        'nis' => $nis,
                        'nama_siswa' => $nama,
                        'kelas' => $kelas,
                        'jurusan' => $jurusan,
                        'username' => $nis,
                        'password' => 'password',
                        'pin_keamanan' => '123456',
                        'saldo' => 0,
                        'status' => $statusNasabah,
                        'kategori' => 'siswa',
                    ]);
                    $sukses++;
                }
            }

            DB::commit();

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
            DB::rollBack();

            return back()->with('error', 'Gagal impor data: '.$e->getMessage());
        }
    }

    // FUNGSI DOWNLOAD TEMPLATE EXCEL (.xlsx) ATAU CSV (.csv)
    public function downloadTemplate(Request $request): Response
    {
        $columns = ['NIS', 'Nama Lengkap', 'Kelas (Contoh: X RPL 1 / XII RPL 1)', 'Jurusan (Contoh: Rekayasa Perangkat Lunak)', 'Status Opsional (aktif/nonaktif)'];
        $contohData = [
            ['10001', 'Contoh Nama Siswa Satu', 'X RPL 1', 'Rekayasa Perangkat Lunak', 'nonaktif'],
            ['10002', 'Contoh Nama Siswa Dua', 'XI RPL 1', 'Rekayasa Perangkat Lunak', 'aktif'],
            ['10003', 'Contoh Nama Siswa Tiga', 'XII RPL 2', 'Rekayasa Perangkat Lunak', 'aktif'],
        ];

        // Format Excel (.xlsx)
        if ($request->query('format') === 'xlsx') {
            if (class_exists(Spreadsheet::class) && class_exists(Xlsx::class)) {
                $spreadsheet = new Spreadsheet;
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Template Import Nasabah');

                $sheet->fromArray([$columns], null, 'A1');
                $sheet->fromArray($contohData, null, 'A2');

                // Auto-size columns
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $response = new StreamedResponse(function () use ($spreadsheet) {
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                });

                $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->headers->set('Content-Disposition', 'attachment; filename="template_import_nasabah.xlsx"');
                $response->headers->set('Cache-Control', 'max-age=0');

                return $response;
            }

            // Fallback native generator jika pustaka PhpSpreadsheet belum ada di server cPanel
            $tempFile = tempnam(sys_get_temp_dir(), 'tpl_xlsx_').'.xlsx';
            self::generateMinimalXlsx(array_merge([$columns], $contohData), $tempFile);

            return response()->download($tempFile, 'template_import_nasabah.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        // Format CSV Standar
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=template_import_nasabah.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($columns, $contohData) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');
            foreach ($contohData as $baris) {
                fputcsv($file, $baris, ';');
            }
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
}
