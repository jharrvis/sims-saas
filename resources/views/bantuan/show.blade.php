<div class="flex flex-col lg:flex-row gap-6 max-w-6xl mx-auto">
    {{-- Navigasi topik --}}
    <aside class="lg:w-64 shrink-0">
        <div class="lg:sticky lg:top-4 rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-3">
            <a href="{{ route('bantuan.index') }}" class="block px-3 py-2 text-sm font-semibold text-slate-500 hover:text-primary mb-1">
                ← Semua Topik
            </a>
            <nav class="space-y-0.5 max-h-[60vh] overflow-y-auto">
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
        <div class="help-content prose prose-slate dark:prose-invert max-w-none
                    prose-headings:text-slate-800 dark:prose-headings:text-white prose-headings:font-bold
                    prose-h1:text-2xl prose-h1:mb-4 prose-h2:text-lg prose-h2:mt-8 prose-h2:mb-3 prose-h2:pb-2 prose-h2:border-b prose-h2:border-slate-200 dark:prose-h2:border-slate-700
                    prose-h3:text-base prose-h3:mt-6 prose-h3:mb-2
                    prose-p:text-sm prose-p:leading-relaxed prose-p:text-slate-600 dark:prose-p:text-slate-300
                    prose-li:text-sm prose-li:text-slate-600 dark:prose-li:text-slate-300
                    prose-strong:text-slate-800 dark:prose-strong:text-white
                    prose-a:text-primary prose-a:no-underline hover:prose-a:underline
                    prose-table:text-sm prose-th:bg-slate-50 dark:prose-th:bg-slate-800 prose-th:px-3 prose-th:py-2 prose-td:px-3 prose-td:py-2 prose-td:border-t prose-td:border-slate-100 dark:prose-td:border-slate-700
                    prose-code:text-[13px] prose-code:bg-slate-100 dark:prose-code:bg-slate-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:before:content-none prose-code:after:content-none
                    prose-blockquote:border-l-4 prose-blockquote:border-primary prose-blockquote:bg-primary/5 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-r-lg prose-blockquote:text-sm">
            {!! $html !!}
        </div>
    </article>
</div>
