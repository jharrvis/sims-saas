<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">Pengguna</h2>
            <p>Akun pengguna sekolah beserta peran dan status aksesnya.</p>
        </div>
        <div class="page-actions">
            @can('pengguna.create')
                <a href="{{ route('pengaturan.pengguna.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Pengguna
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
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <x-icon name="users" class="w-5 h-5" />
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                <p class="text-sm font-semibold">Akun pengguna sekolah</p>
                <p class="mt-1">
                    Setiap pengguna masuk dengan <strong>nomor WhatsApp (OTP)</strong> atau
                    <strong>username + kata sandi</strong>. Buat akun di sini, tetapkan peran, lalu
                    bagikan nomor/kredensialnya. Identitas akun bersifat global; status aktif di sini
                    hanya berlaku untuk sekolah ini.
                </p>
            </div>
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="pengguna-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari pengguna</span>
                <input id="pengguna-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / username / WA…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="pengguna-peran"><span class="sr-only">Filter peran</span>
                <select id="pengguna-peran" wire:model.live="peran" class="select-field">
                    <option value="">Semua peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ \App\Support\RoleCatalog::label($role->name) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="pengguna-status"><span class="sr-only">Filter status</span>
                <select id="pengguna-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="dinonaktifkan">Nonaktif</option>
                </select>
            </label>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Kontak</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
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
                        <tr wire:key="pengguna-{{ $pengguna->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold" aria-hidden="true">{{ $inisial }}</div>
                                    <div class="min-w-0">
                                        <div class="font-medium">{{ $pengguna->name }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $pengguna->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-slate-600 dark:text-slate-300">
                                <div>{{ $pengguna->phone ?? '—' }}</div>
                                @if ($pengguna->email)
                                    <div class="text-xs text-slate-500">{{ $pengguna->email }}</div>
                                @endif
                                @unless ($bisaMasuk)
                                    <span class="badge badge-orange mt-1">Belum bisa masuk</span>
                                @endunless
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($pengguna->roles as $role)
                                        <span class="badge badge-blue">
                                            {{ \App\Support\RoleCatalog::label($role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-warning">Belum ada peran</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $statusAktif ? 'badge-green' : 'badge-gray' }}">
                                    {{ $statusAktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('pengguna.update')
                                        <a href="{{ route('pengaturan.pengguna.edit', $pengguna) }}"
                                           class="p-3.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Ubah {{ $pengguna->name }}">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                        @if ($pengguna->id !== auth()->id())
                                            <button type="button" wire:click="toggleStatus({{ $pengguna->id }})"
                                                    wire:confirm="{{ $statusAktif ? 'Nonaktifkan akses '.$pengguna->name.' ke sekolah ini?' : 'Aktifkan kembali akses '.$pengguna->name.'?' }}"
                                                    class="p-3.5 rounded-lg {{ $statusAktif ? 'text-danger hover:bg-danger/10' : 'text-success hover:bg-success/10' }}"
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
