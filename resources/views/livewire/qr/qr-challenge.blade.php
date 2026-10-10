<div wire:poll.10s="refresh" class="max-w-2xl mx-auto text-center">
    <h1 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Tantangan QR</h1>
    <p class="text-sm text-slate-500 mb-6">Pilih siswa, tampilkan di layar kelas. Berlaku 60 detik, sekali pakai, refresh otomatis.</p>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 mb-5">
        <div class="relative max-w-xs mx-auto">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari siswa…"
                   aria-label="Cari siswa"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            @if ($hasil->isNotEmpty())
                <ul class="absolute z-10 w-full mt-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark shadow-lg text-left max-h-56 overflow-auto">
                    @foreach ($hasil as $s)
                        <li><button wire:click="pilihSiswa({{ $s->id }})" class="w-full px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800 text-left">
                            <span class="font-semibold">{{ $s->nama }}</span> <span class="text-xs text-slate-400">· NIS {{ $s->nis }}</span>
                        </button></li>
                    @endforeach
                </ul>
            @endif
        </div>
        @if ($student_id)
            <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Siswa terpilih: {{ $search }}</p>
        @endif
        @error('student_id') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
        <button wire:click="buat" @disabled(! $student_id)
                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
            <i data-lucide="qr-code" class="w-4 h-4"></i> Buat Kode Baru
        </button>
    </div>

    @if ($token)
        <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-8">
            @if ($student_nama)
                <p class="text-sm text-slate-500 mb-1">Untuk</p>
                <p class="font-bold text-slate-800 dark:text-white mb-4">{{ $student_nama }}</p>
            @endif
            <div class="bg-white p-4 rounded-2xl inline-block">{!! \App\Domains\Pwa\Services\QrLoginService::qrSvg($token, 260) !!}</div>
            <p class="mt-4 text-sm text-slate-500">Berlaku hingga <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($expires_at)->format('H:i:s') }}</span></p>
            <p class="text-xs text-slate-400 mt-1">Siswa memindai kode ini dari halaman login PWA di perangkat bersama.</p>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-12 text-slate-400 text-sm">
            Belum ada kode aktif. Tekan "Buat Kode Baru".
        </div>
    @endif
</div>
