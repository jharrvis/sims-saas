<div class="mx-auto w-full max-w-md">
    <div class="rounded-2xl bg-white shadow-sm p-6 sm:p-8">
        <h1 class="text-xl font-extrabold text-center">Masuk sebagai</h1>
        <p class="text-sm text-slate-500 text-center mt-1 mb-6">Akun Anda memiliki lebih dari satu peran. Pilih salah satu:</p>

        <div class="space-y-3">
            <button wire:click="kePlatform" wire:loading.attr="disabled"
                    class="w-full flex items-center gap-4 rounded-xl border-2 border-slate-200 p-4 text-left hover:border-primary hover:bg-primary/5 transition">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center font-extrabold">S</span>
                <span>
                    <span class="block font-bold">Operator Platform</span>
                    <span class="block text-xs text-slate-500">Kelola tenant, paket, dan langganan SaaS</span>
                </span>
            </button>

            @foreach ($sekolah as $s)
                <button wire:click="keSekolah({{ $s->id }})" wire:loading.attr="disabled"
                        class="w-full flex items-center gap-4 rounded-xl border-2 border-slate-200 p-4 text-left hover:border-primary hover:bg-primary/5 transition">
                    <span class="w-11 h-11 shrink-0 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-extrabold">{{ mb_substr($s->nama, 0, 1) }}</span>
                    <span>
                        <span class="block font-bold">{{ $s->nama }}</span>
                        <span class="block text-xs text-slate-500">Admin sekolah · {{ $s->jenjang }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</div>
