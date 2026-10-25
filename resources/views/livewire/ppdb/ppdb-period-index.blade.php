<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">PPDB</span>
            <h2 class="management-page-title">Periode PPDB</h2>
            <p>Periode penerimaan peserta didik baru beserta jumlah pendaftar dan kuota.</p>
        </div>
        <div class="page-actions">
            {{-- Route ppdb.periode.create belum ada (pembuatan periode hanya lewat API);
                tombol hanya tampil bila route itu didaftarkan, agar halaman tidak crash
                bagi pengguna yang memegang izin ppdb.periode.create. --}}
            @if (\Illuminate\Support\Facades\Route::has('ppdb.periode.create'))
                @can('ppdb.periode.create')
                    <a href="{{ route('ppdb.periode.create') }}" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" /> Buka Periode Baru
                    </a>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($periods as $p)
            <article class="card" wire:key="ppdb-period-{{ $p->id }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold">{{ $p->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $p->academicYear->nama }}</p>
                    </div>
                    @if ($p->isOpen())
                        <span class="badge badge-green">Dibuka</span>
                    @else
                        <span class="badge badge-gray">Ditutup</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-3">
                    {{ $p->tgl_buka->format('d M Y') }} – {{ $p->tgl_tutup->format('d M Y') }} ·
                    {{ $p->applicants_count }} pendaftar
                    @if ($p->kuota > 0) · Kuota {{ $p->kuota }} @endif
                </p>
                <a href="{{ route('ppdb.pendaftar.index', ['period' => $p->id]) }}"
                   class="btn btn-outline mt-4 w-full">
                    Kelola Pendaftar
                </a>
            </article>
        @empty
            <div class="empty-state col-span-full">
                <p>Belum ada periode PPDB.</p>
            </div>
        @endforelse
    </div>

    @if ($periods->hasPages())
        <div class="mt-4">{{ $periods->links() }}</div>
    @endif
</div>
