<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Paket langganan — harga disetujui pemilik produk 9 Okt 2026
 * (flat per sekolah: Basic 499rb, Pro 999rb, Enterprise 2,5jt).
 * Kuota angka adalah default yang dapat diubah operator via panel platform.
 */
class PlanSeeder extends Seeder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function catalog(): array
    {
        return [
            [
                'nama' => 'Basic',
                'slug' => 'basic',
                'deskripsi' => 'Modul inti + add-on à la carte untuk sekolah kecil.',
                'harga_bulanan' => 499000,
                'harga_tahunan' => 4990000,
                'max_siswa' => 500,
                'max_storage_mb' => 10240,
                'max_user_staf' => 50,
                'fitur' => ['akademik', 'kesiswaan', 'kepegawaian', 'pwa', 'laporan_dasar'],
                'aktif' => true,
            ],
            [
                'nama' => 'Pro',
                'slug' => 'pro',
                'deskripsi' => 'Semua 16 modul, all-inclusive.',
                'harga_bulanan' => 999000,
                'harga_tahunan' => 9990000,
                'max_siswa' => 1500,
                'max_storage_mb' => 51200,
                'max_user_staf' => 200,
                'fitur' => ['semua_modul', 'pwa', 'laporan_lengkap', 'api'],
                'aktif' => true,
            ],
            [
                'nama' => 'Enterprise',
                'slug' => 'enterprise',
                'deskripsi' => 'Kustom: mulai Rp2,5 juta/bulan, SLA & domain sendiri.',
                'harga_bulanan' => 2500000,
                'harga_tahunan' => null,
                'max_siswa' => null,
                'max_storage_mb' => null,
                'max_user_staf' => null,
                'fitur' => ['semua_modul', 'pwa', 'laporan_lengkap', 'api', 'sla', 'custom_domain'],
                'aktif' => true,
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::catalog() as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
