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
                'ppdb.periode.view', 'ppdb.periode.create', 'ppdb.periode.update',
                'ppdb.pendaftar.view', 'ppdb.pendaftar.create', 'ppdb.pendaftar.update',
                'ppdb.pendaftar.verify', 'ppdb.pendaftar.approve', 'ppdb.pendaftar.enroll',
                'ppdb.seleksi.input', 'ppdb.pengumuman.create',
                'keuangan.tagihan.view', 'keuangan.tagihan.create', 'keuangan.tagihan.generate',
                'keuangan.bayar.create', 'keuangan.laporan.view',
                'pengumuman.view', 'pengumuman.create', 'pengumuman.update',
                'pengumuman.delete', 'pengumuman.publish',
                'laporan.view', 'laporan.export',
                'erapor.view', 'erapor.cetak', 'erapor.catatan',
                'tatib.view', 'tatib.create', 'tatib.update', 'tatib.delete',
                'bk.view', 'bk.create', 'bk.update', 'bk.delete',
                'perpus.view', 'perpus.buku', 'perpus.pinjam', 'perpus.kembali',
                'ekskul.view', 'ekskul.create', 'ekskul.update', 'ekskul.delete',
                'prestasi.view', 'prestasi.create', 'prestasi.update', 'prestasi.delete',
                'lms.view', 'lms.tugas.create', 'lms.tugas.update', 'lms.tugas.delete',
                'lms.nilai', 'lms.kumpul',
                'broadcast.view', 'broadcast.create', 'broadcast.update', 'broadcast.delete',
                'broadcast.kirim',
                'aset.view', 'aset.create', 'aset.update', 'aset.delete', 'aset.mutasi',
                'uks.view', 'uks.create', 'uks.update', 'uks.delete', 'uks.obat',
                'transportasi.view', 'transportasi.create', 'transportasi.update',
                'transportasi.delete',
                'kantin.view', 'kantin.produk', 'kantin.kasir',
                'survei.view', 'survei.create', 'survei.update', 'survei.delete',
                'survei.isi', 'survei.hasil',
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
                'ppdb.periode.view', 'ppdb.pendaftar.view', 'ppdb.pendaftar.approve',
                'keuangan.tagihan.view', 'keuangan.laporan.view',
                'pengumuman.view', 'pengumuman.publish',
                'laporan.view', 'laporan.export',
                'erapor.view', 'erapor.cetak',
                'bk.view',
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
                'ppdb.periode.view', 'ppdb.pendaftar.view', 'ppdb.pendaftar.create',
                'ppdb.pendaftar.update', 'ppdb.pendaftar.verify',
                'keuangan.tagihan.view', 'keuangan.tagihan.create', 'keuangan.tagihan.generate',
                'keuangan.bayar.create', 'keuangan.laporan.view',
                'pengumuman.view', 'pengumuman.create', 'pengumuman.update', 'pengumuman.publish',
                'laporan.view',
                'tatib.view', 'tatib.create', 'tatib.update',
                'perpus.view', 'perpus.buku', 'perpus.pinjam', 'perpus.kembali',
                'ekskul.view', 'ekskul.create', 'ekskul.update',
                'prestasi.view', 'prestasi.create', 'prestasi.update',
                'dashboard.view',
            ],
            'wali-kelas' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ hanya rombel binaannya (policy)
                'wali-murid.view',
                'jadwal.view',
                'absensi.view', 'absensi.create', 'absensi.update', 'absensi.kunci',
                'nilai.view', 'nilai.release', 'nilai.rekap', // ◐ rombel binaannya
                'erapor.view', 'erapor.cetak', 'erapor.catatan',
                'tatib.view', 'tatib.create', 'tatib.update', 'tatib.delete',
                'bk.view', // catatan konseling rahasia: hanya BK/admin/kepsek
                'dashboard.view',
            ],
            'guru-mapel' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ hanya kelas ampuannya (policy)
                'jadwal.view',
                'absensi.view', 'absensi.create',
                'nilai.view', 'nilai.input', 'nilai.import', 'nilai.update', 'nilai.rekap', // ◐ mapel ampuannya
                'lms.view', 'lms.tugas.create', 'lms.tugas.update', 'lms.tugas.delete', 'lms.nilai',
                'dashboard.view',
            ],
            'guru-bk' => [
                'tahun-ajaran.view',
                'siswa.view',
                'wali-murid.view',
                'absensi.view',
                'nilai.view',
                'bk.view', 'bk.create', 'bk.update', 'bk.delete',
                'dashboard.view',
            ],
            'siswa' => [
                'tahun-ajaran.view',
                'siswa.view', // ◐ datanya sendiri (policy)
                'nilai.view', // ◐ nilai sendiri, hanya yang sudah dirilis (FR-707)
                'lms.view', 'lms.kumpul', // ◐ tugas kelasnya + kumpul jawaban
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
