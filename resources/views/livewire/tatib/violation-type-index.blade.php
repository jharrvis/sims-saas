<div class="max-w-4xl">
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('tatib.index') }}" class="text-sm text-slate-500 hover:text-primary">← Kembali ke Daftar Pelanggaran</a>
    </div>

    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <h2 class="font-semibold text-slate-800 dark:text-white mb-4">{{ $editId ? 'Ubah' : 'Tambah' }} Jenis Pelanggaran</h2>
        <div class="grid sm:grid-cols-4 gap-4">
            <div class="sm:col-span-2">
                <label for="jt-nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama <span class="text-danger">*</span></label>
                <input id="jt-nama" type="text" wire:model="nama" maxlength="150" placeholder="cth. Terlambat masuk kelas"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jt-kat" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kategori</label>
                <select id="jt-kat" wire:model="kategori"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    @foreach (\App\Models\ViolationType::KATEGORI as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="jt-poin" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Poin <span class="text-danger">*</span></label>
                <input id="jt-poin" type="number" wire:model="poin" min="0" max="1000"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('poin') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                {{ $editId ? 'Simpan' : 'Tambah' }}
            </button>
            @if ($editId)
                <button type="button" wire:click="cancelEdit" class="text-sm text-slate-500 hover:text-primary">Batal</button>
            @endif
        </div>
    </form>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Nama</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Kategori</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Poin</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $item->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ \App\Models\ViolationType::KATEGORI[$item->kategori] }}</td>
                        <td class="px-4 py-2.5 font-bold text-danger">{{ $item->poin }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->aktif ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <button wire:click="edit({{ $item->id }})" class="text-xs font-semibold text-primary hover:underline">Ubah</button>
                            <button wire:click="toggle({{ $item->id }})" class="ml-2 text-xs font-semibold text-slate-500 hover:text-primary">
                                {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
