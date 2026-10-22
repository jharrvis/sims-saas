<div class="max-w-5xl mx-auto">
    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if ($error)
        <div class="mb-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm" role="alert">{{ $error }}</div>
    @endif

    @if ($superadmin)
        <div class="mb-4 rounded-lg bg-primary/10 text-primary px-4 py-2.5 text-xs leading-relaxed" role="status">
            Peran <strong>Super Admin</strong> bersifat tetap: selalu memiliki seluruh hak akses dan tidak dapat diubah
            agar tidak ada sekolah yang kehilangan akses penuh.
        </div>
    @endif

    <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-6">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Peran <span class="text-danger">*</span></label>
                <input id="name" type="text" wire:model="name" required @disabled($sistem)
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white font-mono disabled:opacity-60 disabled:cursor-not-allowed">
                <p class="mt-1 text-xs text-slate-500">
                    @if ($sistem)
                        Nama peran bawaan tidak dapat diubah.
                    @else
                        Huruf kecil, angka, dan tanda hubung. Contoh: <span class="font-mono">operator-ppdb</span>.
                    @endif
                </p>
                @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div class="flex sm:justify-end sm:items-start">
                <div class="rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                    <div><strong>{{ count($permissions) }}</strong> dari <strong>{{ $totalPermission }}</strong> hak akses dipilih.</div>
                    <div class="mt-1 text-slate-500">Perubahan berlaku untuk semua pengguna dengan peran ini.</div>
                </div>
            </div>
        </div>

        @unless ($superadmin)
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                <button type="button" wire:click="pilihSemua"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Pilih semua
                </button>
                <button type="button" wire:click="kosongkan"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Kosongkan semua
                </button>
            </div>

            <div class="grid lg:grid-cols-2 gap-3">
                @foreach ($grupPermission as $grup)
                    @php($terpilih = collect($grup['items'])->pluck('name')->intersect($permissions)->count())
                    <div wire:key="grup-{{ $grup['key'] }}"
                         class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ $grup['label'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $terpilih }}/{{ count($grup['items']) }} dipilih</div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" wire:click="pilihGrup('{{ $grup['key'] }}')"
                                        class="px-2 py-1 rounded text-[11px] font-medium text-primary hover:bg-primary/10"
                                        aria-label="Pilih semua {{ $grup['label'] }}">Pilih</button>
                                <button type="button" wire:click="kosongkanGrup('{{ $grup['key'] }}')"
                                        class="px-2 py-1 rounded text-[11px] font-medium text-slate-500 hover:bg-slate-500/10"
                                        aria-label="Kosongkan {{ $grup['label'] }}">Kosongkan</button>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-x-3 gap-y-1.5 px-3.5 py-3">
                            @foreach ($grup['items'] as $item)
                                <label wire:key="perm-{{ $item['name'] }}" class="flex items-start gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" wire:model="permissions" value="{{ $item['name'] }}" class="mt-0.5 rounded">
                                    <span class="min-w-0">
                                        <span class="block font-medium">{{ $item['label'] }}</span>
                                        <span class="block text-[10px] text-slate-400 font-mono truncate">{{ $item['name'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions.*') <p class="text-xs text-danger">{{ $message }}</p> @enderror
        @endunless

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <a href="{{ route('pengaturan.peran') }}"
               class="rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                {{ $superadmin ? 'Kembali' : 'Batal' }}
            </a>
            @unless ($superadmin)
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors disabled:opacity-60">
                    <x-icon name="check" class="w-4 h-4" /> {{ $role ? 'Simpan Hak Akses' : 'Buat Peran' }}
                </button>
            @endunless
        </div>
    </form>
</div>
