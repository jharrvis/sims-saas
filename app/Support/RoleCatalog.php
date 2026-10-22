<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Katalog peran tenant & label permission untuk UI Kelola Pengguna/Peran.
 *
 * Peran bawaan (`RoleSeeder`) tidak boleh dihapus atau di-rename lewat UI
 * karena menjadi kontrak matriks RBAC (Dok 05 §4). Admin sekolah tetap dapat
 * menyesuaikan hak akses peran bawaan (kecuali `superadmin`).
 */
final class RoleCatalog
{
    /** Peran bawaan per sekolah — dibuat oleh RoleSeeder::seedForSchool(). */
    public const SYSTEM_ROLES = [
        'superadmin',
        'admin-sekolah',
        'kepala-sekolah',
        'tata-usaha',
        'wali-kelas',
        'guru-mapel',
        'guru-bk',
        'siswa',
        'orang-tua',
    ];

    /** Peran yang dihitung sebagai administrator sekolah (anti-lockout). */
    public const ADMIN_ROLES = ['superadmin', 'admin-sekolah'];

    /** @var array<string,string> */
    private const LABELS = [
        'superadmin' => 'Super Admin',
        'admin-sekolah' => 'Admin Sekolah',
        'kepala-sekolah' => 'Kepala Sekolah',
        'tata-usaha' => 'Tata Usaha',
        'wali-kelas' => 'Wali Kelas',
        'guru-mapel' => 'Guru Mapel',
        'guru-bk' => 'Guru BK',
        'siswa' => 'Siswa',
        'orang-tua' => 'Orang Tua / Wali',
    ];

    /** @var array<string,string> */
    private const DESCRIPTIONS = [
        'superadmin' => 'Akses penuh seluruh modul sekolah, termasuk pengguna & peran.',
        'admin-sekolah' => 'Mengelola operasional sekolah, pengguna, dan hak akses.',
        'kepala-sekolah' => 'Memantau seluruh data, menyetujui PPDB, dan merilis nilai.',
        'tata-usaha' => 'Input siswa/pegawai, PPDB, keuangan, dan administrasi harian.',
        'wali-kelas' => 'Absensi & nilai kelas binaannya, serta catatan wali kelas.',
        'guru-mapel' => 'Absensi & nilai mata pelajaran yang diampu.',
        'guru-bk' => 'Data siswa dan catatan layanan BK.',
        'siswa' => 'Akses aplikasi siswa (nilai & tugas yang sudah dirilis).',
        'orang-tua' => 'Akses aplikasi orang tua (nilai & absensi anak).',
    ];

    /** @var array<string,string> */
    private const PREFIX_LABELS = [
        'sekolah' => 'Profil Sekolah',
        'tahun-ajaran' => 'Tahun Ajaran',
        'kalender' => 'Kalender Pendidikan',
        'siswa' => 'Data Siswa',
        'wali-murid' => 'Wali Murid',
        'guru' => 'Guru & Pegawai',
        'kelas' => 'Kelas & Rombel',
        'jadwal' => 'Jadwal & Mapel',
        'mapel' => 'Mata Pelajaran',
        'absensi' => 'Absensi',
        'nilai' => 'Nilai',
        'ppdb' => 'PPDB',
        'keuangan' => 'Keuangan',
        'pengumuman' => 'Pengumuman',
        'laporan' => 'Laporan',
        'erapor' => 'E-Rapor',
        'tatib' => 'Tata Tertib',
        'bk' => 'BK & Konseling',
        'perpus' => 'Perpustakaan',
        'ekskul' => 'Ekstrakurikuler',
        'prestasi' => 'Prestasi',
        'lms' => 'LMS / Tugas Online',
        'broadcast' => 'Broadcast',
        'aset' => 'Inventaris & Aset',
        'uks' => 'UKS',
        'transportasi' => 'Antar-Jemput',
        'kantin' => 'Kantin',
        'survei' => 'Survei',
        'alumni' => 'Alumni',
        'organisasi' => 'Organisasi',
        'pengguna' => 'Pengguna',
        'peran' => 'Peran & Hak Akses',
        'qr' => 'QR Login',
        'audit-log' => 'Audit Log',
        'dashboard' => 'Dashboard',
        'impor' => 'Impor Data',
        'dsar' => 'Permintaan Data (DSAR)',
        'webhook' => 'Webhook',
        'langganan' => 'Langganan',
        'modul' => 'Modul Add-on',
        'platform' => 'Platform',
    ];

    /** @var array<string,string> */
    private const ACTION_LABELS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'import' => 'Impor',
        'export' => 'Ekspor',
        'manage' => 'Kelola',
        'assign' => 'Tetapkan',
        'reset-password' => 'Reset Kata Sandi',
        'activate' => 'Aktifkan',
        'close' => 'Tutup',
        'promote' => 'Naikkan Kelas',
        'lock' => 'Kunci',
        'release' => 'Rilis',
        'rekap' => 'Rekap',
        'input' => 'Input',
        'verify' => 'Verifikasi',
        'approve' => 'Setujui',
        'enroll' => 'Daftarkan',
        'generate' => 'Buat Tagihan',
        'kirim' => 'Kirim',
        'cetak' => 'Cetak',
        'catatan' => 'Catatan',
        'kumpul' => 'Kumpulkan',
        'nilai' => 'Beri Nilai',
        'isi' => 'Isi',
        'hasil' => 'Lihat Hasil',
        'obat' => 'Kelola Obat',
        'produk' => 'Kelola Produk',
        'kasir' => 'Kasir',
        'mutasi' => 'Mutasi',
        'kembali' => 'Pengembalian',
        'buku' => 'Kelola Buku',
        'pinjam' => 'Peminjaman',
        'link' => 'Tautkan',
        'bayar' => 'Catat Pembayaran',
        'tagihan' => 'Tagihan',
    ];

    /** @var array<int,string> */
    private const ACTION_ORDER = [
        'view', 'create', 'update', 'delete', 'import', 'export', 'manage',
        'assign', 'reset-password', 'input', 'verify', 'approve', 'enroll',
        'generate', 'activate', 'close', 'promote', 'lock', 'release', 'rekap',
        'kirim', 'cetak', 'catatan', 'kumpul', 'nilai', 'isi', 'hasil',
    ];

    public static function label(string $name): string
    {
        return self::LABELS[$name] ?? ucwords(str_replace(['-', '_'], ' ', $name));
    }

    public static function description(string $name): string
    {
        return self::DESCRIPTIONS[$name] ?? 'Peran kustom buatan sekolah.';
    }

    public static function isSystem(string $name): bool
    {
        return in_array($name, self::SYSTEM_ROLES, true);
    }

    public static function isAdmin(string $name): bool
    {
        return in_array($name, self::ADMIN_ROLES, true);
    }

    /**
     * Urutan tampil: peran bawaan sesuai matriks, lalu peran kustom.
     */
    public static function sortIndex(string $name): int
    {
        $index = array_search($name, self::SYSTEM_ROLES, true);

        return $index === false ? 100 : (int) $index;
    }

    /**
     * Kelompokkan permission per resource agar mudah dicentang.
     *
     * @param  iterable<int, object|string>  $permissions
     * @return array<int, array{key:string,label:string,items:array<int,array{name:string,label:string}>}>
     */
    public static function permissionGroups(iterable $permissions): array
    {
        $groups = [];

        foreach ($permissions as $permission) {
            $name = is_object($permission) ? (string) $permission->name : (string) $permission;
            $segments = explode('.', $name);
            $action = (string) array_pop($segments);
            $prefix = implode('.', $segments);

            $groups[$prefix] ??= [
                'key' => $prefix,
                'label' => self::prefixLabel($prefix),
                'items' => [],
            ];

            $groups[$prefix]['items'][] = [
                'name' => $name,
                'label' => self::ACTION_LABELS[$action] ?? ucwords(str_replace('-', ' ', $action)),
            ];
        }

        foreach ($groups as &$group) {
            usort($group['items'], static function (array $a, array $b): int {
                $rank = self::actionRank($a['name']) <=> self::actionRank($b['name']);

                return $rank !== 0 ? $rank : strcmp($a['label'], $b['label']);
            });
        }
        unset($group);

        usort($groups, static fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return $groups;
    }

    private static function prefixLabel(string $prefix): string
    {
        $segments = explode('.', $prefix);
        $first = (string) array_shift($segments);
        $label = self::PREFIX_LABELS[$first] ?? ucwords(str_replace(['-', '_'], ' ', $first));

        foreach ($segments as $segment) {
            $label .= ' › '.ucwords(str_replace(['-', '_'], ' ', $segment));
        }

        return $label;
    }

    private static function actionRank(string $permission): int
    {
        $segments = explode('.', $permission);
        $action = (string) end($segments);
        $index = array_search($action, self::ACTION_ORDER, true);

        return $index === false ? 99 : (int) $index;
    }
}
