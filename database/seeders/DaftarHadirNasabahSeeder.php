<?php

namespace Database\Seeders;

use App\Helpers\TerbilangHelper;
use App\Http\Controllers\NasabahController;
use App\Models\Nasabah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DaftarHadirNasabahSeeder extends Seeder
{
    /**
     * Run the database seeds from the official attendance file (daftar hadir siswa).
     */
    public function run(): void
    {
        $filePath = database_path('data/daftar_hadir_siswa.csv');
        if (! file_exists($filePath)) {
            $this->command?->warn("File {$filePath} tidak ditemukan.");
            return;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            $this->command?->error("Gagal membuka file {$filePath}.");
            return;
        }

        $currentKelas = null;
        $currentJurusan = null;
        $sukses = 0;
        $diperbarui = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 2000, ',', '"', "\\")) !== false) {
                $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);
                if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                $rowJoined = implode(' ', array_filter($row));

                if (preg_match('/KELAS\s*:\s*([^,\r\n;]+)/i', $rowJoined, $m)) {
                    $currentKelas = trim($m[1]);
                    $currentJurusan = TerbilangHelper::getNamaJurusan($currentKelas);
                    continue;
                }

                if (preg_match('/(DAFTAR HADIR|TAHUN PELAJARAN|MATA PELAJARAN|GURU MATA|JUMLAH SISWA|PERTEMUAN KE|Guru Mata Pelajaran|Bantul,|NIP\.)/i', $rowJoined)) {
                    continue;
                }

                if (
                    isset($row[0], $row[1], $row[2]) &&
                    is_numeric($row[0]) &&
                    is_numeric($row[1]) &&
                    strlen($row[1]) >= 4 &&
                    ! empty($row[2])
                ) {
                    $nis = $row[1];
                    $nama = rtrim($row[2], " *\t\n\r\0\x0B");
                    $kelas = $currentKelas ?: '-';
                    $jurusan = $currentJurusan ?: TerbilangHelper::getNamaJurusan($kelas);

                    // Kelas 10 (X) = nonaktif (belum aktif), Kelas 11 & 12 = aktif
                    if (preg_match('/\b(?:X|10)\b/i', $kelas) && ! preg_match('/\b(?:XI|XII|11|12)\b/i', $kelas)) {
                        $statusNasabah = 'nonaktif';
                    } else {
                        $statusNasabah = 'aktif';
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
                        $no_rekening = NasabahController::generateNoRekening($jurusan, 'siswa', $nis);

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
                            'status' => $statusNasabah,
                            'kategori' => 'siswa',
                        ]);
                        $sukses++;
                    }
                }
            }
            fclose($handle);
            DB::commit();

            $this->command?->info("Seeder selesai! {$sukses} nasabah baru terdaftar, {$diperbarui} data nasabah diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->command?->error("Seeder gagal: {$e->getMessage()}");
        }
    }
}

