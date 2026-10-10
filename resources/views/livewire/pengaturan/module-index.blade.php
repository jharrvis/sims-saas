<div class="max-w-4xl">
    @include('livewire.pengaturan._tabs')
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <p class="text-sm text-slate-500 mb-4">Modul inti selalu aktif. Modul add-on dapat diaktifkan/dinonaktifkan sesuai paket langganan sekolah.</p>

    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($modules as $module)
            @php $tm = $module->tenantModule; $aktif = $module->isInti() || ($tm && $tm->aktif); @endphp
            <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-slate-800 dark:text-white">{{ $module->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $module->deskripsi ?? '—' }}</p>
                        <div class="flex gap-2 mt-2">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">
                                {{ $module->kategori === 'inti' ? 'Inti' : 'Add-on' }}
                            </span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $aktif ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                                {{ $aktif ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                    </div>
                    @if (! $module->isInti())
                        @can('sekolah.update')
                            <button wire:click="toggle({{ $module->id }})" role="switch" aria-checked="{{ $aktif ? 'true' : 'false' }}"
                                    aria-label="Toggle modul {{ $module->nama }}"
                                    class="relative shrink-0 w-11 h-6 rounded-full transition {{ $aktif ? 'bg-success' : 'bg-slate-300 dark:bg-slate-600' }}">
                                <span class="absolute top-0.5 {{ $aktif ? 'right-0.5' : 'left-0.5' }} w-5 h-5 rounded-full bg-white shadow"></span>
                            </button>
                        @endcan
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
