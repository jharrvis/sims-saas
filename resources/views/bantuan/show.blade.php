<div class="flex flex-col lg:flex-row gap-6 max-w-6xl mx-auto">
    {{-- Navigasi topik --}}
    <aside class="lg:w-64 shrink-0">
        <div class="lg:sticky lg:top-4 rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-3">
            <a href="{{ route('bantuan.index') }}" class="block px-3 py-2 text-sm font-semibold text-slate-500 hover:text-primary mb-1">
                ← Semua Topik
            </a>
            <nav class="space-y-0.5 max-h-[60vh] overflow-y-auto pb-1">
                @foreach ($topics as $slug => [$judul, $deskripsi, $ikon])
                    <a href="{{ route('bantuan.show', $slug) }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm {{ $slug === $active ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        <i data-lucide="{{ $ikon }}" class="w-4 h-4 shrink-0"></i>
                        {{ $judul }}
                    </a>
                @endforeach
            </nav>
        </div>
    </aside>

    {{-- Isi artikel --}}
    <article class="flex-1 min-w-0 rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 sm:p-8">
        <div class="help-content">
            {!! $html !!}
        </div>
    </article>
</div>
