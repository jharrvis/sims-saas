@php
$tabs = array_values(array_filter([
    ['route' => 'pengaturan.profil', 'label' => 'Profil Sekolah'],
    ['route' => 'pengaturan.tahun-ajaran', 'label' => 'Tahun Ajaran'],
    ['route' => 'pengaturan.modul', 'label' => 'Modul Add-on'],
    auth()->user()?->can('pengguna.view') ? ['route' => 'pengaturan.pengguna', 'match' => 'pengaturan.pengguna*', 'label' => 'Pengguna'] : null,
    auth()->user()?->can('peran.view') ? ['route' => 'pengaturan.peran', 'match' => 'pengaturan.peran*', 'label' => 'Peran & Akses'] : null,
    ['route' => 'pengaturan.keamanan', 'label' => 'Keamanan Akun'],
]));
@endphp
<div class="flex gap-1 mb-5 border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px whitespace-nowrap {{ request()->routeIs($tab['match'] ?? $tab['route']) ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
