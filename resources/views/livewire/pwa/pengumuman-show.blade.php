<div class="screen app-screen screen-enter">
    <a href="{{ route('pwa.pengumuman') }}" class="pwa-back-link">
        <x-icon name="arrow-left" class="h-4 w-4" /> Semua pengumuman
    </a>

    <article class="announcement-detail {{ $item->penting ? 'is-important' : '' }}">
        <header>
            <span class="row-icon {{ $item->penting ? 'coral' : 'blue' }}">
                <x-icon :name="$item->penting ? 'circle-alert' : 'megaphone'" class="h-5 w-5" />
            </span>
            <span class="announcement-detail-heading">
                <span class="announcement-meta">
                    <span class="status-chip {{ $item->penting ? 'danger' : 'info' }}">{{ $item->penting ? 'PENTING' : 'INFORMASI' }}</span>
                    <time datetime="{{ $item->tayang_dari?->toDateString() }}">{{ $item->tayang_dari?->locale('id')->isoFormat('dddd, D MMMM Y') }}</time>
                </span>
                <h1>{{ $item->judul }}</h1>
            </span>
        </header>
        <div class="announcement-body">{{ $item->isi }}</div>
    </article>

    <p class="security-note">
        <x-icon name="shield-check" class="h-4 w-4" />
        <span>Pengumuman ini diterbitkan melalui akun resmi sekolah.</span>
    </p>
</div>
