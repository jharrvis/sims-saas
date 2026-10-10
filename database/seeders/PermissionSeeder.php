<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * Katalog permission (Dok 05). Format {resource}.{aksi}; level platform
 * memakai tiga segmen {domain}.{resource}.{aksi} dengan guard `platform`.
 * Nama permission imutable setelah go-live (Dok 05 §2).
 */
class PermissionSeeder extends Seeder
{
    /**
     * @return array<string, array<int,string>>
     */
    public static function catalog(): array
    {
        return [
            // guard web (tenant)
            'web' => [
                'sekolah.view', 'sekolah.create', 'sekolah.update', 'sekolah.switch',
                'tahun-ajaran.view', 'tahun-ajaran.create', 'tahun-ajaran.update',
                'tahun-ajaran.activate', 'tahun-ajaran.close',
                'kalender.manage',
                'siswa.view', 'siswa.create', 'siswa.update', 'siswa.delete',
                'siswa.import', 'siswa.export',
                'wali-murid.view', 'wali-murid.create', 'wali-murid.update',
                'wali-murid.delete', 'wali-murid.link', 'wali-murid.export',
                'guru.view', 'guru.create', 'guru.update', 'guru.delete',
                'guru.import', 'guru.export', 'guru.assign',
                'kelas.view', 'kelas.create', 'kelas.update', 'kelas.delete',
                'kelas.promote',
                'jadwal.view', 'jadwal.create', 'jadwal.update', 'jadwal.delete',
                'absensi.view', 'absensi.create', 'absensi.update', 'absensi.delete',
                'absensi.kunci',
                'nilai.view', 'nilai.input', 'nilai.import', 'nilai.update',
                'nilai.komponen', 'nilai.lock', 'nilai.unlock', 'nilai.release',
                'nilai.rekap',
                'pengguna.view', 'pengguna.create', 'pengguna.update',
                'pengguna.delete', 'pengguna.reset-password',
                'peran.view', 'peran.create', 'peran.update', 'peran.delete',
                'peran.assign',
                'dashboard.view',
                'audit-log.view', 'audit-log.export',
            ],
            // guard platform (operator SaaS)
            'platform' => [
                'platform.tenant.view', 'platform.tenant.create', 'platform.tenant.suspend',
                'platform.plan.manage', 'platform.plan.view',
                'platform.module.toggle', 'platform.module.view',
                'platform.metrics.view',
                'platform.audit.view',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::catalog() as $guard => $names) {
            foreach ($names as $name) {
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => $guard],
                );
            }
        }
    }
}
