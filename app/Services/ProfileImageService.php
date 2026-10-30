<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileImageService
{
    /**
     * MIME types yang diizinkan secara ketat.
     *
     * @var array<string, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * Ekstensi file yang diizinkan secara ketat.
     *
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * Pola berbahaya untuk mendeteksi virus / web shell / polyglot yang disamarkan dalam file gambar.
     *
     * @var list<string>
     */
    private const DANGEROUS_PATTERNS = [
        '/<\?php/i',
        '/<\?=/i',
        '/<\?\s+/i',
        '/<script/i',
        '/<html/i',
        '/<!doctype/i',
        '/<iframe/i',
        '/eval\s*\(/i',
        '/passthru\s*\(/i',
        '/system\s*\(/i',
        '/shell_exec\s*\(/i',
        '/exec\s*\(/i',
        '/proc_open\s*\(/i',
        '/popen\s*\(/i',
        '/base64_decode\s*\(/i',
        '/\$_GET/i',
        '/\$_POST/i',
        '/\$_REQUEST/i',
        '/\$_SERVER/i',
        '/\$_COOKIE/i',
        '/\$_FILES/i',
    ];

    /**
     * Validasi mendalam, sanitasi anti-malware melalui re-encoding GD,
     * dan simpan foto profil dengan nama acak yang aman.
     *
     * @throws ValidationException
     */
    public function validateAndStore(UploadedFile $file, ?string $oldPath = null): string
    {
        // 1. Validasi Keberadaan & Integritas File Upload
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'foto' => 'File yang diunggah mengalami kerusakan atau gagal terunggah sempurna.',
            ]);
        }

        // 2. Validasi Ekstensi Klien (Hanya PNG dan JPG/JPEG)
        $clientExtension = strtolower($file->getClientOriginalExtension());
        if (! in_array($clientExtension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'foto' => 'Format file "'.$clientExtension.'" ditolak! Hanya format gambar PNG dan JPG yang diizinkan.',
            ]);
        }

        $realPath = $file->getRealPath();

        // 3. Inspeksi Magic Bytes Asli menggunakan PHP Fileinfo (Mencegah spoofing MIME type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo ? finfo_file($finfo, $realPath) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (! isset(self::ALLOWED_MIME_TYPES[$detectedMime])) {
            throw ValidationException::withMessages([
                'foto' => 'File ditolak! Tipe konten asli file ('.$detectedMime.') bukan merupakan gambar PNG atau JPG yang sah.',
            ]);
        }

        // 4. Validasi Struktur Header & Geometri Gambar menggunakan getimagesize()
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false) {
            throw ValidationException::withMessages([
                'foto' => 'File ditolak! Struktur header gambar rusak atau bukan file gambar riil.',
            ]);
        }

        $imageType = $imageInfo[2] ?? 0;
        if (! in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw ValidationException::withMessages([
                'foto' => 'Tipe struktur internal gambar tidak valid. Hanya JPG/JPEG dan PNG yang diperbolehkan.',
            ]);
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width < 50 || $height < 50) {
            throw ValidationException::withMessages([
                'foto' => 'Dimensi gambar terlalu kecil (minimal 50x50 piksel).',
            ]);
        }

        if ($width > 4000 || $height > 4000) {
            throw ValidationException::withMessages([
                'foto' => 'Dimensi gambar terlalu besar (maksimal 4000x4000 piksel).',
            ]);
        }

        // 5. Deep Scan Konten Biner: Deteksi Virus / Polyglot / Web Shell Embedded
        $rawBytes = @file_get_contents($realPath);
        if ($rawBytes !== false) {
            // Cek karakter null byte injection
            if (str_contains($file->getClientOriginalName(), "\0")) {
                throw ValidationException::withMessages([
                    'foto' => 'Peringatan Keamanan: Terdeteksi upaya manipulasi nama file (Null Byte Injection)!',
                ]);
            }

            foreach (self::DANGEROUS_PATTERNS as $pattern) {
                if (preg_match($pattern, $rawBytes)) {
                    throw ValidationException::withMessages([
                        'foto' => 'Peringatan Keamanan: File ditolak karena terdeteksi mengandung skrip / kode berbahaya yang disamarkan (Polyglot / Malware)!',
                    ]);
                }
            }
        }

        // 6. Sanitasi & Rekonstruksi Ulang Gambar via PHP GD (Image Re-encoding)
        // Proses ini membuang semua metadata tersembunyi (EXIF), payload biner parasit,
        // dan hanya merekonstruksi ulang piksel murni ke dalam kanvas baru.
        try {
            $isPng = ($detectedMime === 'image/png');

            if ($isPng) {
                $sourceImage = @imagecreatefrompng($realPath);
            } else {
                $sourceImage = @imagecreatefromjpeg($realPath);
            }

            if (! $sourceImage) {
                throw new Exception('Gagal membaca data piksel gambar.');
            }

            // Batasi resolusi maksimal untuk efisiensi penyimpanan & rendering (maks 1000px)
            $maxDimension = 1000;
            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $targetWidth = $maxDimension;
                    $targetHeight = (int) round(($height / $width) * $maxDimension);
                } else {
                    $targetHeight = $maxDimension;
                    $targetWidth = (int) round(($width / $height) * $maxDimension);
                }
            } else {
                $targetWidth = $width;
                $targetHeight = $height;
            }

            $cleanCanvas = imagecreatetruecolor($targetWidth, $targetHeight);

            if ($isPng) {
                // Pertahankan alpha channel transparansi untuk PNG
                imagealphablending($cleanCanvas, false);
                imagesavealpha($cleanCanvas, true);
                $transparent = imagecolorallocatealpha($cleanCanvas, 0, 0, 0, 127);
                imagefilledrectangle($cleanCanvas, 0, 0, $targetWidth, $targetHeight, $transparent);
            } else {
                // Background putih untuk format JPEG
                $white = imagecolorallocate($cleanCanvas, 255, 255, 255);
                imagefilledrectangle($cleanCanvas, 0, 0, $targetWidth, $targetHeight, $white);
            }

            // Salin dan render ulang piksel ke kanvas bersih
            imagecopyresampled(
                $cleanCanvas,
                $sourceImage,
                0, 0, 0, 0,
                $targetWidth, $targetHeight,
                $width, $height
            );

            // 7. Simpan dengan nama file acak yang aman (Mencegah Path Traversal & Penamaan Eksekusi)
            $targetExtension = $isPng ? 'png' : 'jpg';
            $safeRandomName = 'avatar_'.Str::random(40).'.'.$targetExtension;
            $relativeStoragePath = 'profil/'.$safeRandomName;

            Storage::disk('public')->makeDirectory('profil');
            $fullDestinationPath = Storage::disk('public')->path($relativeStoragePath);

            if ($isPng) {
                imagepng($cleanCanvas, $fullDestinationPath, 8);
            } else {
                imagejpeg($cleanCanvas, $fullDestinationPath, 85);
            }

            // Bersihkan memori GD
            imagedestroy($sourceImage);
            imagedestroy($cleanCanvas);

            // 8. Hapus Foto Profil Lama jika Ada
            $this->deleteOldAvatar($oldPath);

            return $relativeStoragePath;

        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'foto' => 'Gagal memproses gambar profil: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Hapus foto lama secara aman dari penyimpanan publik.
     */
    public function deleteOldAvatar(?string $oldPath): void
    {
        if (empty($oldPath)) {
            return;
        }

        // Hapus path relatif jika ada di disk public
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Kompatibilitas dengan path lama seperti public/pegawai/
        if (Storage::disk('public')->exists('pegawai/'.$oldPath)) {
            Storage::disk('public')->delete('pegawai/'.$oldPath);
        }
    }
}
