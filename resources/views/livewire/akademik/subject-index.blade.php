<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Data Master</span>
            <h2 class="management-page-title">Mata Pelajaran</h2>
            <p>Daftar mata pelajaran beserta kelompok dan KKM.</p>
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="subject-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari mata pelajaran</span>
                <input id="subject-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari mapel…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Kelompok</th>
                        <th scope="col">KKM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $s)
                        <tr wire:key="subject-{{ $s->id }}">
                            <td class="font-mono font-semibold text-primary">{{ $s->kode }}</td>
                            <td class="font-medium">{{ $s->nama }}</td>
                            <td class="capitalize">{{ $s->kelompok }}</td>
                            <td>{{ $s->kkm_default }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                Tidak ada data mata pelajaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subjects->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">
                {{ $subjects->links() }}
            </div>
        @endif
    </div>
</div>
