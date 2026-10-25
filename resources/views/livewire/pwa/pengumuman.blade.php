<div class="screen app-screen screen-enter">
    <div class="page-title">
        <span class="overline">INFORMASI SEKOLAH</span>
        <h1>Pengumuman</h1>
        <p>Informasi resmi yang sesuai dengan peran dan kelas aktif Anda.</p>
    </div>

    <div class="announcement-stack">
        @forelse ($items as $item)
            <a href="{{ route('pwa.pengumuman.show', $item) }}" class="announcement-card {{ $item->penting ? 'is-important' : '' }}" wire:key="announcement-{{ $item->id }}">
                <span class="row-icon {{ $item->penting ? 'coral' : 'blue' }}">
                    <x-icon :name="$item->penting ? 'circle-alert' : 'megaphone'" class="h-5 w-5" />
                </span>
                <span class="announcement-copy">
                    <span class="announcement-meta">
                        @if ($item->penting)
                            <span class="status-chip danger">PENTING</span>
                        @else
                            <span class="status-chip info">INFORMASI</span>
                        @endif
                        <time datetime="{{ $item->tayang_dari?->toDateString() }}">{{ $item->tayang_dari?->locale('id')->isoFormat('D MMM Y') }}</time>
                    </span>
                    <strong>{{ $item->judul }}</strong>
                    <small>{{ strip_tags($item->isi) }}</small>
                </span>
                <x-icon name="chevron-right" class="h-4 w-4 text-slate-400" />
            </a>
        @empty
            <div class="surface-card pwa-empty-state pwa-empty-state-centered">
                <span class="row-icon blue"><x-icon name="megaphone" class="h-5 w-5" /></span>
                <span><strong>Belum ada pengumuman</strong><small>Informasi sekolah yang ditujukan kepada Anda akan tampil di sini.</small></span>
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div class="pwa-pagination">{{ $items->links() }}</div>
    @endif
</div>
