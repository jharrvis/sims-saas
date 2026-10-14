<?php

declare(strict_types=1);

namespace App\Domains\Kesiswaan\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Upload foto siswa dengan kompresi GD (tanpa dependensi baru).
 *
 * - Perbaiki orientasi EXIF (foto HP sering terotasi).
 * - Resize maks 512×512 px (pertahankan rasio) → file kecil, tidak membebani server.
 * - Simpan sebagai JPEG kualitas 80 (target < 150 KB untuk foto biasa).
 * - File lama dihapus saat foto diganti/dihapus.
 */
class StudentPhotoService
{
    public const MAX_DIMENSION = 512;

    public const JPEG_QUALITY = 80;

    /**
     * Kompresi + simpan foto baru. Mengembalikan path relatif di disk public.
     *
     * @param  string  $folder  folder tujuan, cth. 'siswa-foto' atau 'guru-foto'
     */
    public function store(UploadedFile $file, int $schoolId, string $folder = 'siswa-foto'): string
    {
        $image = $this->decode($file);

        $image = $this->applyExifOrientation($image, $file);
        $image = $this->resize($image);
        $image = $this->flattenOnWhite($image);

        $tmp = tempnam(sys_get_temp_dir(), $folder.'-').'.jpg';
        imagejpeg($image, $tmp, self::JPEG_QUALITY);
        imagedestroy($image);

        try {
            $path = $folder.'/'.$schoolId.'/'.Str::uuid().'.jpg';
            Storage::disk('public')->put($path, file_get_contents($tmp));

            return $path;
        } finally {
            @unlink($tmp);
        }
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @return resource|\GdImage
     */
    private function decode(UploadedFile $file)
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()) ?: '');

        if ($image === false) {
            throw ValidationException::withMessages([
                'foto' => 'File foto tidak dapat dibaca. Coba unggah ulang file lain.',
            ]);
        }

        return $image;
    }

    /**
     * @param  resource|\GdImage  $image
     * @return resource|\GdImage
     */
    private function applyExifOrientation($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());

        if (! is_array($exif) || empty($exif['Orientation'])) {
            return $image;
        }

        return match ((int) $exif['Orientation']) {
            2 => $this->flip($image, IMG_FLIP_HORIZONTAL),
            3 => imagerotate($image, 180, 0),
            4 => $this->flip($image, IMG_FLIP_VERTICAL),
            5 => $this->flip(imagerotate($image, -90, 0), IMG_FLIP_HORIZONTAL),
            6 => imagerotate($image, -90, 0),
            7 => $this->flip(imagerotate($image, 90, 0), IMG_FLIP_HORIZONTAL),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    /**
     * @param  resource|\GdImage  $image
     * @return resource|\GdImage
     */
    private function flip($image, int $mode)
    {
        imageflip($image, $mode);

        return $image;
    }

    /**
     * @param  resource|\GdImage  $image
     * @return resource|\GdImage
     */
    private function resize($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $max = max($width, $height);

        if ($max <= self::MAX_DIMENSION) {
            return $image;
        }

        $scale = self::MAX_DIMENSION / $max;
        $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
        imagedestroy($image);

        if ($resized === false) {
            throw new RuntimeException('Gagal mengubah ukuran foto.');
        }

        return $resized;
    }

    /**
     * Ratakan transparansi (PNG/WebP) ke latar putih sebelum disimpan sebagai JPEG.
     *
     * @param  resource|\GdImage  $image
     * @return resource|\GdImage
     */
    private function flattenOnWhite($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $canvas;
    }
}
