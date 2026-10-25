<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">Modul Add-on</h2>
            <p>Modul inti selalu aktif. Modul add-on dapat diaktifkan/dinonaktifkan sesuai paket langganan sekolah.</p>
        </div>
    </div>

    @include('livewire.pengaturan._tabs')

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($modules as $module)
            @php $tm = $module->tenantModule; $aktif = $module->isInti() || ($tm && $tm->aktif); @endphp
            <article class="card" wire:key="modul-{{ $module->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold">{{ $module->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $module->deskripsi ?? '—' }}</p>
                        <div class="flex gap-2 mt-2">
                            <span class="badge badge-gray">
                                {{ $module->kategori === 'inti' ? 'Inti' : 'Add-on' }}
                            </span>
                            <span class="badge {{ $aktif ? 'badge-green' : 'badge-gray' }}">
                                {{ $aktif ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                    </div>
                    @if (! $module->isInti())
                        @can('sekolah.update')
                            <button wire:click="toggle({{ $module->id }})" role="switch" aria-checked="{{ $aktif ? 'true' : 'false' }}"
                                    aria-label="Toggle modul {{ $module->nama }}"
                                    class="relative shrink-0 inline-flex items-center h-11 px-1">
                                <span class="relative w-11 h-6 rounded-full transition {{ $aktif ? 'bg-success' : 'bg-slate-300 dark:bg-slate-600' }}">
                                    <span class="absolute top-0.5 {{ $aktif ? 'right-0.5' : 'left-0.5' }} w-5 h-5 rounded-full bg-white shadow"></span>
                                </span>
                            </button>
                        @endcan
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</div>
