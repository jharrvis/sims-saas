<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Peran level tenant per sekolah (Dok 05 §1.1, §4).
 * Dibuat per tenant karena Spatie teams aktif — dipanggil saat provisioning
 * sekolah baru maupun via seeder untuk sekolah yang sudah ada.
 */
class RoleSeeder extends Seeder
{
    /**
     * Matriks peran × permission (Dok 05 §4.1–4.2, subset MVP).
     * ◐ (terbatas: rombel binaannya / datanya sendiri) diwujudkan via policy,
     * bukan permission terpisah.
     *
     * @return array<string, array<int,string>>
     */
    public static function matrix(): array
    {
        $semua = Permission::where('guard_name', 'web')->pluck('name')->all();

        return [
            'superadmin' => $semua,
            'admin-sekolah' => [
                'sekolah.view', 'sekolah.update',
                'tahun-ajaran.view', 'tahun-ajaran.create', 'tahun-ajaran.update',
                'tahun-ajaran.activate', 'tahun-ajaran.close', 'kalender.manage',
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
                'nilai.komponen', 'nilai.lock', 'nilai.release', 'nilai.rekap',
                'pengguna.view', 'pengguna.create', 'pengguna.update',
                'pengguna.delete', 'pengguna.reset-password',
                'peran.view', 'peran.create', 'peran.update', 'peran.delete',
                'peran.assign',
                'dashboard.view',
                'audit-log.view', 'audit-log.export',
            ],
            'kepala-sekolah' => [
                'sekolah.view',
                'tahun-ajaran.view', 'tahun-ajaran.activate', 'tahun-ajaran.close',
                'siswa.view', 'siswa.export',
                'wali-murid.view', 'wali-murid.export',
                'guru.view',
                'kelas.view',
                'jadwal.view',
                'absensi.view',
                'nilai.view', 'nilai.komponen', 'nilai.release', 'nilai.rekap',
                'dashboard.view',
                'audit-log.view',
            ],
            'tata-usaha' => [
                'tahun-ajaran.view',
                'siswa.view', 'siswa.create', 'siswa.update', 'siswa.import', 'siswa.export',
                'wali-murid.view', 'wali-murid.create', 'wali-murid.update',
                'wali-murid.link', 'wali-murid.export',
                'guru.view',
                'kelas.view',
                'jadwal.view', 'jadwal.create', 'jadwal.update',
                'absensi.view',
                'nilai.view', 'nilai.rekap',
                'dashboard.view',
            ],
            'wali-kelas' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ hanya rombel binaannya (policy)
                'wali-murid.view',
                'jadwal.view',
                'absensi.view', 'absensi.create', 'absensi.update', 'absensi.kunci',
                'nilai.view', 'nilai.release', 'nilai.rekap', // ◐ rombel binaannya
                'dashboard.view',
            ],
            'guru-mapel' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ hanya kelas ampuannya (policy)
                'jadwal.view',
                'absensi.view', 'absensi.create',
                'nilai.view', 'nilai.input', 'nilai.import', 'nilai.update', 'nilai.rekap', // ◐ mapel ampuannya
                'dashboard.view',
            ],
            'guru-bk' => [
                'tahun-ajaran.view',
                'siswa.view',
                'wali-murid.view',
                'absensi.view',
                'nilai.view',
                'dashboard.view',
            ],
            'siswa' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ datanya sendiri (policy)
                'nilai.view', // ◐ nilai sendiri, hanya yang sudah dirilis (FR-707)
                'dashboard.view',
            ],
            'orang-tua' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ anaknya (policy)
                'wali-murid.view',
                'nilai.view', // ◐ nilai anak, hanya yang sudah dirilis (FR-707)
                'dashboard.view',
            ],
        ];
    }

    public function run(): void
    {
        foreach (School::all() as $school) {
            $this->seedForSchool($school->id);
        }
    }

    public static function seedForSchool(int $schoolId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);

        $permissions = Permission::where('guard_name', 'web')->get()->keyBy('name');

        foreach (self::matrix() as $roleName => $names) {
            // findOrCreate (bukan firstOrCreate Eloquent) agar team_id terisi otomatis
            $role = Role::findOrCreate($roleName, 'web');

            $role->syncPermissions(
                collect($names)
                    ->map(fn (string $n) => $permissions->get($n))
                    ->filter()
                    ->all()
            );
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
