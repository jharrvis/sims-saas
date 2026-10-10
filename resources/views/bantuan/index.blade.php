<div class="max-w-5xl mx-auto">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Pusat Bantuan</h1>
        <p class="text-sm text-slate-500 mt-2">Panduan penggunaan aplikasi untuk admin, guru, dan staf sekolah.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($topics as $slug => [$judul, $deskripsi, $ikon])
            <a href="{{ route('bantuan.show', $slug) }}"
               class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 hover:border-primary hover:shadow-md transition group">
                <div class="flex items-start gap-3">
                    <span class="shrink-0 w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <i data-lucide="{{ $ikon }}" class="w-5 h-5"></i>
                    </span>
                    <span>
                        <span class="block font-semibold text-slate-800 dark:text-white group-hover:text-primary">{{ $judul }}</span>
                        <span class="block text-xs text-slate-500 mt-1">{{ $deskripsi }}</span>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</div>
