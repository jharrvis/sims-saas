<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('ekskul.index') }}" class="text-sm text-slate-500 hover:text-primary">← Ekstrakurikuler</a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari prestasi…"
                       aria-label="Cari prestasi"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="tingkat" aria-label="Filter tingkat"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua tingkat</option>
                @foreach (\App\Models\Achievement::TINGKAT as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @can('prestasi.create')
            <button wire:click="$set('showForm', true)" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Catat Prestasi
            </button>
        @endcan
    </div>

    @if ($showForm)
        @can('prestasi.create')
            <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5 grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="siswa" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Siswa <span class="text-danger">*</span></label>
                    <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    <select wire:model="student_id" aria-label="Pilih siswa"
                            class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                        <option value="">— Pilih siswa —</option>
                        @foreach ($siswaList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Prestasi <span class="text-danger">*</span></label>
                    <input id="nama" type="text" wire:model="nama" maxlength="200" placeholder="Juara 1 Olimpiade Matematika"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="kategori" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kategori</label>
                    <select id="kategori" wire:model="kategori"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                        @foreach (\App\Models\Achievement::KATEGORI as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tingkat" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tingkat</label>
                    <select id="tingkat" wire:model="tingkat"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                        @foreach (\App\Models\Achievement::TINGKAT as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tanggal" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tanggal</label>
                    <input id="tanggal" type="date" wire:model="tanggal"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="keterangan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Keterangan</label>
                    <input id="keterangan" type="text" wire:model="keterangan" maxlength="2000"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div class="sm:col-span-2 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Simpan</button>
                    <button type="button" wire:click="$set('showForm', false)" class="text-sm text-slate-500 hover:text-primary">Batal</button>
                </div>
            </form>
        @endcan
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Siswa</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Prestasi</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Tingkat</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->student->nama }}</td>
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-slate-700 dark:text-slate-200">{{ $item->nama }}</span>
                            <span class="text-xs text-slate-400">{{ \App\Models\Achievement::KATEGORI[$item->kategori] }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-warning/15 text-amber-600">{{ \App\Models\Achievement::TINGKAT[$item->tingkat] }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $item->tanggal?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            @can('prestasi.delete')
                                <button wire:click="destroy({{ $item->id }})" wire:confirm="Hapus prestasi ini?"
                                        class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada prestasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
