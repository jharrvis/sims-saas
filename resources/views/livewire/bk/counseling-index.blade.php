<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kesiswaan / BK</span>
            <h2 class="management-page-title">Konseling</h2>
            <p>Sesi konseling siswa beserta topik, status, dan tindak lanjutnya.</p>
        </div>
        <div class="page-actions">
            @can('bk.create')
                <a href="{{ route('bk.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Jadwalkan Konseling
                </a>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="field-search management-search-field" for="konseling-search">
                <x-icon name="search" class="h-4 w-4" /><span class="sr-only">Cari sesi konseling</span>
                <input id="konseling-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…" autocomplete="off">
                <span wire:loading wire:target="search" class="management-inline-spinner" aria-hidden="true"></span>
            </label>
            <label class="management-filter-field" for="konseling-status"><span class="sr-only">Filter status</span>
                <select id="konseling-status" wire:model.live="status" class="select-field">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\CounselingSession::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/10 dark:border-amber-800 px-4 py-3 text-xs text-amber-700 dark:text-amber-300 flex items-start gap-2">
        <x-icon name="lock" class="h-4 w-4 shrink-0 mt-0.5" />
        <span>Catatan konseling bersifat <strong>rahasia</strong> — hanya dapat diakses guru BK, admin sekolah, dan kepala sekolah.</span>
    </div>

    <div class="space-y-3">
        @forelse ($items as $item)
            <article class="card" wire:key="konseling-{{ $item->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold">{{ $item->topik }}</h3>
                            <span class="badge {{ $item->status === 'selesai' ? 'badge-green' : ($item->status === 'batal' ? 'badge-gray' : 'badge-orange') }}">
                                {{ \App\Models\CounselingSession::STATUS[$item->status] }}
                            </span>
                            <span class="badge badge-gray">
                                {{ \App\Models\CounselingSession::JENIS[$item->jenis] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">
                            {{ $item->student->nama }} · {{ $item->student->nis }} ·
                            {{ $item->tanggal->format('d M Y') }}
                            @if ($item->counselor) · Konselor: {{ $item->counselor->name }} @endif
                        </p>
                        @if ($item->tindak_lanjut)
                            <p class="text-xs text-slate-500 mt-1">Tindak lanjut: {{ $item->tindak_lanjut }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @can('bk.update')
                            <a href="{{ route('bk.edit', $item) }}" class="text-xs font-semibold text-primary hover:underline">Ubah / Isi Hasil</a>
                            @if ($item->status === 'terjadwal')
                                <button wire:click="tandaiSelesai({{ $item->id }})" class="text-xs font-semibold text-success hover:underline">Tandai Selesai</button>
                            @endif
                        @endcan
                        @can('bk.delete')
                            <button wire:click="destroy({{ $item->id }})" wire:confirm="Hapus sesi ini?" class="text-xs font-semibold text-danger hover:underline">Hapus</button>
                        @endcan
                    </div>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <p>Belum ada sesi konseling terjadwal.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
