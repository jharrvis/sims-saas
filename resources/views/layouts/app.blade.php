@php
    $groups = \App\Support\Navigation::school();
    $quickActions = [];

    if (\Illuminate\Support\Facades\Route::has('siswa.create') && auth()->user()?->can('siswa.create')) {
        $quickActions[] = ['label' => 'Tambah Siswa', 'href' => route('siswa.create'), 'icon' => 'user-plus'];
    }

    if (\Illuminate\Support\Facades\Route::has('nilai.input') && auth()->user()?->can('nilai.input')) {
        $quickActions[] = ['label' => 'Input Nilai', 'href' => route('nilai.input'), 'icon' => 'pen-line'];
    }

    if (\Illuminate\Support\Facades\Route::has('absensi.index') && auth()->user()?->can('absensi.view')) {
        $quickActions[] = ['label' => 'Absensi Hari Ini', 'href' => route('absensi.index'), 'icon' => 'clipboard-check'];
    }
@endphp

<x-desktop-shell
    :title="$title ?? 'Dashboard'"
    surface="school"
    :groups="$groups"
    :quick-actions="$quickActions"
    home-route="dashboard"
    logout-route="logout"
    :workspace-name="app(\App\Tenancy\TenantContext::class)->school()?->nama"
    workspace-sub="Workspace Sekolah"
>
    {!! $slot ?? '' !!}
</x-desktop-shell>
