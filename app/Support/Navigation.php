<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Server-side navigation for the two desktop surfaces.
 *
 * Menu visibility is a convenience, never authorization: every route keeps its
 * own middleware/permission check. Items are grouped so the desktop shell can
 * match the reference information architecture without losing existing links.
 */
final class Navigation
{
    /**
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public static function school(): array
    {
        $user = auth()->user();

        $items = [
            'utama' => [
                self::item('Dashboard', 'dashboard', 'layout-dashboard', active: 'dashboard'),
                self::item('Panel Platform', 'platform.dashboard', 'shield-check', active: 'platform.*', visible: (bool) $user?->is_platform),
                self::item('Ganti Sekolah', 'tenant.switch', 'repeat', active: 'tenant.switch', visible: ($user?->activeMemberships()->count() ?? 0) > 1),
            ],
            'data-master' => [
                self::item('Data Siswa', 'siswa.index', 'users', active: 'siswa.*', permission: 'siswa.view'),
                self::item('QR Login', 'qr.kartu', 'qr-code', active: 'qr.*', permission: 'qr.view'),
                self::item('Data Guru', 'guru.index', 'graduation-cap', active: 'guru.*', permission: 'guru.view'),
                self::item('Data Kelas', 'kelas.index', 'school', active: 'kelas.*', permission: 'kelas.view'),
                self::item('Jadwal Pelajaran', 'jadwal.index', 'calendar-days', active: 'jadwal.*,mapel.*', permission: 'jadwal.view'),
                self::item('PPDB', 'ppdb.index', 'user-plus', active: 'ppdb.*', permission: 'ppdb.periode.view'),
            ],
            'akademik' => [
                self::item('Absensi Siswa', 'absensi.index', 'clipboard-check', active: 'absensi.*', permission: 'absensi.view'),
                self::item('Data Nilai', 'nilai.input', 'award', active: 'nilai.*', permission: 'nilai.input'),
                self::item('E-Rapor', 'erapor.index', 'file-text', active: 'erapor.*', permission: 'erapor.view'),
                self::item('Tugas Online', 'lms.index', 'pen-line', active: 'lms.*', permission: 'lms.view'),
            ],
            'kesiswaan' => [
                self::item('Ekstrakurikuler', 'ekskul.index', 'medal', active: 'ekskul.*', permission: 'ekskul.view'),
                self::item('BK & Konseling', 'bk.index', 'heart-handshake', active: 'bk.*', permission: 'bk.view'),
                self::item('Tata Tertib', 'tatib.index', 'shield-alert', active: 'tatib.*', permission: 'tatib.view'),
                self::item('Organisasi', 'organisasi.index', 'users-round', active: 'organisasi.*', permission: 'organisasi.view'),
                self::item('Alumni', 'alumni.index', 'briefcase', active: 'alumni.*', permission: 'alumni.view'),
                self::item('Survei', 'survei.index', 'clipboard-list', active: 'survei.*', permission: 'survei.view'),
            ],
            'operasional' => [
                self::item('Keuangan SPP', 'keuangan.index', 'wallet', active: 'keuangan.*', permission: 'keuangan.tagihan.view'),
                self::item('Inventaris', 'aset.index', 'package', active: 'aset.*', permission: 'aset.view'),
                self::item('Perpustakaan', 'perpus.index', 'library', active: 'perpus.*', permission: 'perpus.view'),
                self::item('UKS', 'uks.index', 'heart-pulse', active: 'uks.*', permission: 'uks.view'),
                self::item('Antar-Jemput', 'transportasi.index', 'bus', active: 'transportasi.*', permission: 'transportasi.view'),
                self::item('Kantin', 'kantin.index', 'store', active: 'kantin.*', permission: 'kantin.view'),
                self::item('Broadcast', 'broadcast.index', 'send', active: 'broadcast.*', permission: 'broadcast.view'),
            ],
            'informasi' => [
                self::item('Pengumuman', 'pengumuman.index', 'megaphone', active: 'pengumuman.*', permission: 'pengumuman.view'),
                self::item('Laporan', 'laporan.index', 'bar-chart-3', active: 'laporan.*', permission: 'laporan.view'),
                self::item('Pengaturan', 'pengaturan.profil', 'settings', active: 'pengaturan.*', permission: 'sekolah.view', exclude: 'pengaturan.pengguna*,pengaturan.peran*'),
                self::item('Kelola Pengguna', 'pengaturan.pengguna', 'user-cog', active: 'pengaturan.pengguna*', permission: 'pengguna.view'),
                self::item('Peran & Akses', 'pengaturan.peran', 'shield', active: 'pengaturan.peran*', permission: 'peran.view'),
                self::item('Keamanan Akun', 'pengaturan.keamanan', 'shield-check', active: 'pengaturan.keamanan'),
                self::item('Langganan', 'langganan', 'credit-card', active: 'langganan', permission: 'langganan.view'),
                self::item('Pusat Bantuan', 'bantuan.index', 'circle-help', active: 'bantuan.*'),
            ],
        ];

        return self::groups($items);
    }

    /**
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public static function platform(): array
    {
        $user = auth()->user();

        $items = [
            'platform' => [
                self::item('Dashboard Platform', 'platform.dashboard', 'layout-dashboard', active: 'platform.dashboard', platformPermission: 'platform.metrics.view'),
                self::item('Tenant', 'platform.tenants', 'school', active: 'platform.tenants*', platformPermission: 'platform.tenant.view'),
                self::item('Paket', 'platform.plans', 'package', active: 'platform.plans', platformPermission: 'platform.plan.view'),
                self::item('Modul', 'platform.modules', 'puzzle', active: 'platform.modules', platformPermission: 'platform.module.view'),
                self::item('Langganan', 'platform.subscriptions', 'repeat', active: 'platform.subscriptions', platformPermission: 'platform.tenant.view'),
                self::item('Tagihan', 'platform.invoices', 'receipt', active: 'platform.invoices', platformPermission: 'platform.tenant.view'),
                self::item('Audit Log', 'platform.audit', 'scroll-text', active: 'platform.audit', platformPermission: 'platform.audit.view'),
            ],
            'akses' => [
                self::item('Dashboard Sekolah', 'dashboard', 'school', active: 'dashboard', visible: $user?->activeMemberships()->exists() ?? false),
            ],
        ];

        return self::groups($items);
    }

    /**
     * Flatten visible navigation into command-palette entries.
     *
     * @param  array<int, array{label: string, items: array<int, array<string, mixed>>}>  $groups
     * @return array<int, array{title: string, href: string, icon: string, group: string}>
     */
    public static function commands(array $groups): array
    {
        $commands = [];

        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                $commands[] = [
                    'title' => $item['label'],
                    'href' => $item['href'],
                    'icon' => $item['icon'],
                    'group' => $group['label'],
                ];
            }
        }

        return $commands;
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $groups
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    private static function groups(array $groups): array
    {
        $labels = [
            'utama' => 'UTAMA',
            'data-master' => 'DATA MASTER',
            'akademik' => 'AKADEMIK',
            'kesiswaan' => 'KESISWAAN',
            'operasional' => 'OPERASIONAL',
            'informasi' => 'INFORMASI & SISTEM',
            'platform' => 'PLATFORM',
            'akses' => 'AKSES',
        ];

        $result = [];

        foreach ($groups as $key => $items) {
            $visible = array_values(array_filter($items, fn (array $item): bool => $item['visible']));

            if ($visible !== []) {
                $result[] = ['label' => $labels[$key] ?? strtoupper($key), 'items' => $visible];
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private static function item(
        string $label,
        string $route,
        string $icon,
        string $active,
        ?string $permission = null,
        ?string $platformPermission = null,
        ?bool $visible = null,
        ?string $exclude = null,
    ): array {
        $allowed = $visible ?? self::allowed($permission, $platformPermission);
        $href = Route::has($route) ? route($route) : '#';

        return [
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'active' => self::isActive($active, $exclude),
            'visible' => $allowed && $href !== '#',
        ];
    }

    private static function allowed(?string $permission, ?string $platformPermission): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        try {
            if ($platformPermission !== null) {
                return $user->hasPermissionTo($platformPermission, 'platform');
            }

            if ($permission !== null) {
                return Gate::forUser($user)->allows($permission);
            }
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    private static function isActive(string $pattern, ?string $exclude = null): bool
    {
        $patterns = array_filter(array_map('trim', explode(',', $pattern)));
        $excludes = $exclude ? array_filter(array_map('trim', explode(',', $exclude))) : [];

        foreach ($excludes as $excluded) {
            if (request()->routeIs($excluded)) {
                return false;
            }
        }

        foreach ($patterns as $candidate) {
            if (request()->routeIs($candidate)) {
                return true;
            }
        }

        return false;
    }
}
