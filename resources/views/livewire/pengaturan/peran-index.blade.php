<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">Peran &amp; Hak Akses</h2>
            <p>Peran bawaan mengikuti matriks RBAC. Sekolah dapat menyesuaikan hak aksesnya atau membuat peran kustom. Peran hanya berlaku untuk sekolah ini.</p>
        </div>
        <div class="page-actions">
            @can('peran.create')
                <a href="{{ route('pengaturan.peran.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Buat Peran
                </a>
            @endcan
        </div>
    </div>

    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif
    @if ($info)
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ $info }}</span>
        </div>
    @endif
    @if ($error)
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger" role="alert">{{ $error }}</div>
    @endif

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Peran</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Jenis</th>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Hak Akses</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        @php $sistem = \App\Support\RoleCatalog::isSystem($role->name); @endphp
                        <tr wire:key="peran-{{ $role->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 shrink-0 rounded-xl {{ $sistem ? 'bg-primary/10 text-primary' : 'bg-slate-500/10 text-slate-500' }} flex items-center justify-center">
                                        <x-icon name="shield" class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium">{{ \App\Support\RoleCatalog::label($role->name) }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $role->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-slate-600 dark:text-slate-300 max-w-md">
                                {{ \App\Support\RoleCatalog::description($role->name) }}
                            </td>
                            <td>
                                <span class="badge {{ $sistem ? 'badge-blue' : 'badge-gray' }}">
                                    {{ $sistem ? 'Bawaan' : 'Kustom' }}
                                </span>
                            </td>
                            <td class="text-center text-sm text-slate-600 dark:text-slate-300">{{ $role->users_count }}</td>
                            <td class="text-center text-sm text-slate-600 dark:text-slate-300">{{ $role->permissions_count }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('peran.update')
                                        <a href="{{ route('pengaturan.peran.edit', $role) }}"
                                           class="p-3.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700"
                                           aria-label="{{ $role->name === 'superadmin' ? 'Lihat' : 'Ubah' }} {{ \App\Support\RoleCatalog::label($role->name) }}">
                                            <x-icon name="{{ $role->name === 'superadmin' ? 'eye' : 'pencil' }}" class="w-4 h-4" />
                                        </a>
                                    @endcan
                                    @can('peran.delete')
                                        @unless ($sistem)
                                            <button type="button" wire:click="hapus({{ $role->id }})"
                                                    data-sims-confirm="Hapus peran {{ \App\Support\RoleCatalog::label($role->name) }}? Tindakan ini tidak dapat dibatalkan."
                                                    class="p-3.5 rounded-lg text-danger hover:bg-danger/10"
                                                    aria-label="Hapus {{ \App\Support\RoleCatalog::label($role->name) }}">
                                                <x-icon name="trash-2" class="w-4 h-4" />
                                            </button>
                                        @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                Belum ada peran pada sekolah ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-3 text-xs text-slate-500">
        Peran bawaan tidak dapat dihapus atau diganti namanya. Peran <strong>Super Admin</strong> selalu memiliki seluruh hak akses.
    </p>
</div>
