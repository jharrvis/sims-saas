<div>
    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm" role="status">{{ session('ok') }}</div>
    @endif
    @if ($info)
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm" role="status">{{ $info }}</div>
    @endif
    @if ($error)
        <div class="mb-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm" role="alert">{{ $error }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
        <div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Peran &amp; Hak Akses</h2>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Peran bawaan mengikuti matriks RBAC. Sekolah dapat menyesuaikan hak aksesnya atau
                membuat peran kustom. Peran hanya berlaku untuk sekolah ini.
            </p>
        </div>
        @can('peran.create')
            <a href="{{ route('pengaturan.peran.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors shrink-0">
                <x-icon name="plus" class="w-4 h-4" /> Buat Peran
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Peran</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Deskripsi</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Jenis</th>
                        <th class="py-3 px-5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pengguna</th>
                        <th class="py-3 px-5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Hak Akses</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        @php $sistem = \App\Support\RoleCatalog::isSystem($role->name); @endphp
                        <tr wire:key="peran-{{ $role->id }}" class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 shrink-0 rounded-xl {{ $sistem ? 'bg-primary/10 text-primary' : 'bg-slate-500/10 text-slate-500' }} flex items-center justify-center">
                                        <x-icon name="shield" class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 dark:text-white">{{ \App\Support\RoleCatalog::label($role->name) }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $role->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-xs text-slate-600 dark:text-slate-300 max-w-md">
                                {{ \App\Support\RoleCatalog::description($role->name) }}
                            </td>
                            <td class="py-3 px-5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $sistem ? 'bg-primary/10 text-primary' : 'bg-slate-500/10 text-slate-500' }}">
                                    {{ $sistem ? 'Bawaan' : 'Kustom' }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-center text-sm text-slate-600 dark:text-slate-300">{{ $role->users_count }}</td>
                            <td class="py-3 px-5 text-center text-sm text-slate-600 dark:text-slate-300">{{ $role->permissions_count }}</td>
                            <td class="py-3 px-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('peran.update')
                                        <a href="{{ route('pengaturan.peran.edit', $role) }}"
                                           class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700"
                                           aria-label="{{ $role->name === 'superadmin' ? 'Lihat' : 'Ubah' }} {{ \App\Support\RoleCatalog::label($role->name) }}">
                                            <x-icon name="{{ $role->name === 'superadmin' ? 'eye' : 'pencil' }}" class="w-4 h-4" />
                                        </a>
                                    @endcan
                                    @can('peran.delete')
                                        @unless ($sistem)
                                            <button type="button" wire:click="hapus({{ $role->id }})"
                                                    wire:confirm="Hapus peran {{ \App\Support\RoleCatalog::label($role->name) }}? Tindakan ini tidak dapat dibatalkan."
                                                    class="p-2 rounded-lg text-danger hover:bg-danger/10"
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
