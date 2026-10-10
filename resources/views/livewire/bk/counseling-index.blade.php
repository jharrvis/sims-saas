<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…"
                       aria-label="Cari sesi konseling"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="status" aria-label="Filter status"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua status</option>
                @foreach (\App\Models\CounselingSession::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @can('bk.create')
            <a href="{{ route('bk.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="plus" class="w-4 h-4"></i> Jadwalkan Konseling
            </a>
        @endcan
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/10 dark:border-amber-800 px-4 py-3 mb-5 text-xs text-amber-700 dark:text-amber-300 flex items-start gap-2">
        <i data-lucide="lock" class="w-4 h-4 shrink-0 mt-0.5"></i>
        <span>Catatan konseling bersifat <strong>rahasia</strong> — hanya dapat diakses guru BK, admin sekolah, dan kepala sekolah.</span>
    </div>

    <div class="space-y-3">
        @forelse ($items as $item)
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-slate-800 dark:text-white">{{ $item->topik }}</h3>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $item->status === 'selesai' ? 'bg-success/10 text-success' : ($item->status === 'batal' ? 'bg-slate-100 text-slate-500 dark:bg-slate-800' : 'bg-warning/15 text-amber-600') }}">
                                {{ \App\Models\CounselingSession::STATUS[$item->status] }}
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">
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
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-10 text-center text-sm text-slate-500">
                Belum ada sesi konseling terjadwal.
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $items->links() }}</div>
</div>
