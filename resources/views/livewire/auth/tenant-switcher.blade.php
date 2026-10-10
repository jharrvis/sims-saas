<div class="w-full max-w-lg rounded-2xl bg-white dark:bg-boxdark shadow-2xl p-8 border border-slate-100 dark:border-slate-800">
    <div class="text-center mb-6">
        <div class="mx-auto w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="building-2" class="w-7 h-7 text-primary"></i>
        </div>
        <h1 class="text-xl font-black text-slate-800 dark:text-white">Pilih Sekolah Aktif</h1>
        <p class="text-xs text-slate-500 mt-1">Akun global Anda terdaftar di {{ $schools->count() }} sekolah</p>
    </div>

    @if ($error)
        <div class="mb-5 rounded-lg bg-danger/10 border border-danger/20 px-4 py-3 text-sm text-danger" role="alert">{{ $error }}</div>
    @endif

    <div class="space-y-3">
        @foreach ($schools as $school)
            <button wire:click="switch({{ $school->id }})"
                    class="w-full flex items-center gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary hover:bg-primary/5 transition-colors text-left">
                <span class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold shrink-0">
                    {{ strtoupper(substr($school->nama, 0, 1)) }}
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block font-semibold text-slate-800 dark:text-white truncate">{{ $school->nama }}</span>
                    <span class="block text-xs text-slate-500">{{ $school->jenjang }} · {{ $school->slug }}</span>
                </span>
                <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400 shrink-0"></i>
            </button>
        @endforeach
    </div>
</div>
