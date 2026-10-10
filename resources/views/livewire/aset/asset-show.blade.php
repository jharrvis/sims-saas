<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('aset.index') }}" class="text-sm text-slate-500 hover:text-primary">← Semua Aset</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $asset->nama }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $asset->kode }} · {{ $asset->category->nama }}
                    @if ($asset->lokasi) · Lokasi: {{ $asset->lokasi }} @endif
                </p>
                <p class="text-sm text-slate-500 mt-0.5">
                    Kondisi: <span class="font-semibold">{{ \App\Models\Asset::KONDISI[$asset->kondisi] }}</span>
                    @if ($asset->harga_beli) · Rp {{ number_format((float) $asset->harga_beli, 0, ',', '.') }} @endif
                </p>
            </div>
            @can('aset.mutasi')
                <button wire:click="$set('showForm', true)" class="shrink-0 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                    Catat Mutasi
                </button>
            @endcan
        </div>
    </div>

    @if ($showForm)
        @can('aset.mutasi')
            <form wire:submit="simpanMutasi" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5 grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jenis Mutasi</label>
                    <select wire:model.live="jenis"
                            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                        @foreach (['pindah' => 'Pindah Lokasi', 'kondisi' => 'Ubah Kondisi', 'keluar' => 'Barang Keluar'] as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tanggal</label>
                    <input type="date" wire:model="tgl"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                @if ($jenis === 'pindah')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Dari</label>
                        <input type="text" wire:model="dari" maxlength="150" value="{{ $asset->lokasi }}"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Ke (lokasi baru)</label>
                        <input type="text" wire:model="ke" maxlength="150"
                               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    </div>
                @elseif ($jenis === 'kondisi')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kondisi Baru</label>
                        <select wire:model="ke"
                                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                            @foreach (\App\Models\Asset::KONDISI as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Catatan</label>
                    <input type="text" wire:model="catatan" maxlength="2000"
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
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 font-semibold text-slate-800 dark:text-white text-sm">
            Riwayat Mutasi
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Jenis</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Dari → Ke</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mutations as $m)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $m->tgl?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ \App\Models\AssetMutation::JENIS[$m->jenis] }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">
                            {{ $m->jenis === 'kondisi' && $m->ke ? (\App\Models\Asset::KONDISI[$m->ke] ?? $m->ke) : ($m->dari ?? '—').' → '.($m->ke ?? '—') }}
                        </td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $m->catatan ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada mutasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $mutations->links() }}</div>
</div>
