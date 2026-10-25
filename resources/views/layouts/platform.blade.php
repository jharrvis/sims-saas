@php
    $groups = \App\Support\Navigation::platform();
    $quickActions = [];

    if (\Illuminate\Support\Facades\Route::has('platform.tenants.create') && auth()->user()?->hasPermissionTo('platform.tenant.create', 'platform')) {
        $quickActions[] = ['label' => 'Tambah Tenant', 'href' => route('platform.tenants.create'), 'icon' => 'plus'];
    }

    if (\Illuminate\Support\Facades\Route::has('platform.audit')) {
        $quickActions[] = ['label' => 'Lihat Audit Log', 'href' => route('platform.audit'), 'icon' => 'scroll-text'];
    }
@endphp

<x-desktop-shell
    :title="$title ?? 'Platform'"
    surface="platform"
    :groups="$groups"
    :quick-actions="$quickActions"
    home-route="platform.dashboard"
    logout-route="platform.logout"
    workspace-name="SIMS Platform"
    workspace-sub="Operator Platform"
>
    {!! $slot ?? '' !!}
</x-desktop-shell>
