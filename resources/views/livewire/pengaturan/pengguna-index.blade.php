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

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 px-5 py-4 mb-5">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <x-icon name="users" class="w-5 h-5" />
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                <p class="text-sm font-semibold text-slate-800 dark:text-white">Akun pengguna sekolah</p>
                <p class="mt-1">
                    Setiap pengguna masuk dengan <strong>nomor WhatsApp (OTP)</strong> atau
                    <strong>username + kata sandi</strong>. Buat akun di sini, tetapkan peran, lalu
                    bagikan nomor/kredensialnya. Identitas akun bersifat global; status aktif di sini
                    hanya berlaku untuk sekolah ini.
                </p>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="relative w-full sm:max-w-xs">
            <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / username / WA…"
                   aria-label="Cari pengguna"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary dark:text-white">
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="peran" aria-label="Filter peran"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ \App\Support\RoleCatalog::label($role->name) }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="dinonaktifkan">Nonaktif</option>
            </select>
            @can('pengguna.create')
                <a href="{{ route('pengaturan.pengguna.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <x-icon name="plus" class="w-4 h-4" /> Tambah Pengguna
                </a>
            @endcan
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pengguna</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Kontak</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Peran</th>
                        <th class="py-3 px-5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                        <th class="py-3 px-5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $pengguna)
                        @php
                            $membership = $pengguna->memberships->first();
                            $statusAktif = $membership?->status === 'aktif';
                            $namaParts = preg_split('/\s+/', trim($pengguna->name));
                            $inisial = strtoupper(mb_substr($namaParts[0] ?? '?', 0, 1).(count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : ''));
                            $bisaMasuk = filled($pengguna->phone) || filled($pengguna->password);
                        @endphp
                        <tr wire:key="pengguna-{{ $pengguna->id }}" class="border-b border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold" aria-hidden="true">{{ $inisial }}</div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 dark:text-white">{{ $pengguna->name }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $pengguna->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-sm text-slate-600 dark:text-slate-300">
                                <div>{{ $pengguna->phone ?? '—' }}</div>
                                @if ($pengguna->email)
                                    <div class="text-xs text-slate-500">{{ $pengguna->email }}</div>
                                @endif
                                @unless ($bisaMasuk)
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-warning/10 text-warning">Belum bisa masuk</span>
                                @endunless
                            </td>
                            <td class="py-3 px-5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($pengguna->roles as $role)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                            {{ \App\Support\RoleCatalog::label($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-warning">Belum ada peran</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3 px-5">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $statusAktif ? 'bg-success/10 text-success' : 'bg-slate-500/10 text-slate-500' }}">
                                    {{ $statusAktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('pengguna.update')
                                        <a href="{{ route('pengaturan.pengguna.edit', $pengguna) }}"
                                           class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Ubah {{ $pengguna->name }}">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                        @if ($pengguna->id !== auth()->id())
                                            <button type="button" wire:click="toggleStatus({{ $pengguna->id }})"
                                                    wire:confirm="{{ $statusAktif ? 'Nonaktifkan akses '.$pengguna->name.' ke sekolah ini?' : 'Aktifkan kembali akses '.$pengguna->name.'?' }}"
                                                    class="p-2 rounded-lg {{ $statusAktif ? 'text-danger hover:bg-danger/10' : 'text-success hover:bg-success/10' }}"
                                                    aria-label="{{ $statusAktif ? 'Nonaktifkan' : 'Aktifkan' }} {{ $pengguna->name }}">
                                                <x-icon name="{{ $statusAktif ? 'user-x' : 'user-check' }}" class="w-4 h-4" />
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                Belum ada pengguna pada sekolah ini.
                                @can('pengguna.create')
                                    <a href="{{ route('pengaturan.pengguna.create') }}" class="text-primary font-medium hover:underline">Tambah pengguna pertama</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <p class="mt-3 text-xs text-slate-500">
        {{ $jumlahAktif }} pengguna aktif · {{ $jumlahNonaktif }} nonaktif.
    </p>
</div>
