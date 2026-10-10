<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * Registri modul: inti (selalu aktif) + add-on (flag per tenant).
 */
class ModuleSeeder extends Seeder
{
    /**
     * @return array<int, array{kode:string, nama:string, kategori:string}>
     */
    public static function registry(): array
    {
        return [
            // Inti — selalu aktif
            ['kode' => 'kesiswaan', 'nama' => 'Kesiswaan', 'kategori' => 'inti'],
            ['kode' => 'kepegawaian', 'nama' => 'Kepegawaian', 'kategori' => 'inti'],
            ['kode' => 'akademik', 'nama' => 'Akademik (Jadwal, Absensi, Nilai)', 'kategori' => 'inti'],
            // Add-on
            ['kode' => 'ppdb', 'nama' => 'PPDB Online', 'kategori' => 'addon'],
            ['kode' => 'keuangan', 'nama' => 'Keuangan & SPP', 'kategori' => 'addon'],
            ['kode' => 'e_rapor', 'nama' => 'E-Rapor', 'kategori' => 'addon'],
            ['kode' => 'pengumuman', 'nama' => 'Pengumuman', 'kategori' => 'addon'],
            ['kode' => 'tata_tertib', 'nama' => 'Tata Tertib & Poin', 'kategori' => 'addon'],
            ['kode' => 'bk_konseling', 'nama' => 'BK & Konseling', 'kategori' => 'addon'],
            ['kode' => 'perpustakaan', 'nama' => 'Perpustakaan Digital', 'kategori' => 'addon'],
            ['kode' => 'ekstrakurikuler', 'nama' => 'Ekstrakurikuler & Prestasi', 'kategori' => 'addon'],
            ['kode' => 'lms_mini', 'nama' => 'LMS Mini / Tugas Online', 'kategori' => 'addon'],
            ['kode' => 'broadcast', 'nama' => 'Notifikasi Terjadwal / Broadcast', 'kategori' => 'addon'],
            ['kode' => 'inventaris', 'nama' => 'Inventaris & Aset', 'kategori' => 'addon'],
            ['kode' => 'uks', 'nama' => 'UKS & Kesehatan', 'kategori' => 'addon'],
            ['kode' => 'transportasi', 'nama' => 'Antar-Jemput / Transportasi', 'kategori' => 'addon'],
            ['kode' => 'kantin', 'nama' => 'Kantin / Koperasi Digital', 'kategori' => 'addon'],
            ['kode' => 'survei', 'nama' => 'Survei & Evaluasi', 'kategori' => 'addon'],
        ];
    }

    public function run(): void
    {
        foreach (self::registry() as $m) {
            Module::firstOrCreate(['kode' => $m['kode']], $m);
        }
    }
}
