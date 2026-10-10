<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <a href="{{ route('platform.tenants') }}" class="text-sm text-slate-500 hover:underline">← Kembali ke daftar tenant</a>
    <h1 class="text-2xl font-extrabold mt-1 mb-1">{{ $school->nama }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $school->jenjang }}@if($school->npsn) · NPSN {{ $school->npsn }}@endif · Status: <span class="font-semibold">{{ str_replace('_', ' ', $school->status_langganan) }}</span></p>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Langganan --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-bold mb-3">Langganan</h2>
            @if ($subscription)
                <dl class="text-sm space-y-1.5 mb-4">
                    <div class="flex justify-between"><dt class="text-slate-500">Paket</dt><dd class="font-semibold">{{ $subscription->plan?->nama }} ({{ $subscription->periode }})</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-semibold">{{ $subscription->status }}</dd></div>
                    @if ($subscription->trial_berakhir_pada)
                        <div class="flex justify-between"><dt class="text-slate-500">Trial berakhir</dt><dd class="font-semibold">{{ $subscription->trial_berakhir_pada->format('d M Y') }}</dd></div>
                    @endif
                    @if ($subscription->periode_berakhir_pada)
                        <div class="flex justify-between"><dt class="text-slate-500">Periode berakhir</dt><dd class="font-semibold">{{ $subscription->periode_berakhir_pada->format('d M Y') }}</dd></div>
                    @endif
                </dl>
            @else
                <p class="text-sm text-slate-400 mb-4">Belum ada langganan.</p>
            @endif
            <h3 class="text-sm font-bold mb-2">Buat / Ganti Langganan</h3>
            <div class="flex flex-wrap gap-2 items-end">
                <div>
                    <label class="text-xs text-slate-500">Paket</label>
                    <select wire:model="plan_id" class="block rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Pilih —</option>
                        @foreach ($plans as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} — Rp{{ number_format($p->harga_bulanan, 0, ',', '.') }}/bln</option>
                        @endforeach
                    </select>
                    @error('plan_id') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-slate-500">Periode</label>
                    <select wire:model="periode" class="block rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
                <label class="inline-flex items-center gap-1.5 text-sm pb-2">
                    <input type="checkbox" wire:model="trial" class="accent-primary"> Trial 30 hari
                </label>
                <button wire:click="buatLangganan" wire:confirm="Buat langganan baru? Langganan aktif sebelumnya dibatalkan."
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </div>
        </div>

        {{-- Kuota --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-bold mb-3">Kuota & Pemakaian ({{ now()->format('M Y') }})</h2>
            <dl class="text-sm space-y-1.5 mb-3">
                <div class="flex justify-between"><dt class="text-slate-500">Siswa aktif</dt><dd class="font-semibold">{{ $siswaCount }}</dd></div>
                @foreach ($usages as $u)
                    <div class="flex justify-between"><dt class="text-slate-500">{{ $u->metrik }}</dt><dd class="font-semibold">{{ $u->nilai }}</dd></div>
                @endforeach
            </dl>
            <h3 class="text-sm font-bold mb-2">Override Kuota</h3>
            @foreach ($overrides as $ov)
                <div class="flex items-center justify-between text-sm py-1.5 border-b border-slate-100 last:border-0">
                    <span>{{ $ov->metrik }} → <span class="font-semibold">{{ $ov->batas_custom ?? 'tanpa batas' }}</span>
                        @if (! $ov->isBerlaku()) <span class="text-xs text-danger">(kedaluwarsa)</span> @endif
                    </span>
                    <button wire:click="hapusOverride({{ $ov->id }})" wire:confirm="Hapus override ini?" class="text-xs text-danger font-semibold">Hapus</button>
                </div>
            @endforeach
            <div class="flex flex-wrap gap-2 items-end mt-3">
                <div>
                    <label class="text-xs text-slate-500">Metrik</label>
                    <select wire:model="ov_metrik" class="block rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">—</option>
                        <option value="siswa_aktif">siswa_aktif</option>
                        <option value="user_staf">user_staf</option>
                        <option value="storage_mb">storage_mb</option>
                        <option value="wa_bulanan">wa_bulanan</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Batas (kosong = tanpa batas)</label>
                    <input type="number" wire:model="ov_batas" min="0" class="block rounded-lg border border-slate-200 py-2 px-3 text-sm w-32">
                </div>
                <div class="flex-1 min-w-40">
                    <label class="text-xs text-slate-500">Alasan</label>
                    <input type="text" wire:model="ov_alasan" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <button wire:click="tambahOverride" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            </div>
            @error('ov_metrik') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            @error('ov_alasan') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Modul --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-bold mb-3">Modul Tenant</h2>
            <div class="space-y-1.5 max-h-80 overflow-y-auto">
                @foreach ($modules as $m)
                    @php $tm = $tenantModules->get($m->id); @endphp
                    <div class="flex items-center justify-between text-sm py-1.5 border-b border-slate-100 last:border-0">
                        <span>{{ $m->nama }} <span class="text-xs text-slate-400">({{ $m->kategori }})</span></span>
                        <button wire:click="toggleModule({{ $m->id }})"
                                class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $tm?->aktif ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-500' }}">
                            {{ $tm?->aktif ? 'AKTIF' : 'NONAKTIF' }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tagihan --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="font-bold mb-3">Tagihan Langganan</h2>
            @forelse ($invoices as $inv)
                <div class="flex items-center justify-between text-sm py-1.5 border-b border-slate-100 last:border-0">
                    <span>{{ $inv->nomor }} <span class="text-slate-400">· {{ $inv->status }}</span></span>
                    <span class="font-semibold">Rp{{ number_format($inv->total, 0, ',', '.') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada tagihan.</p>
            @endforelse
            <a href="{{ route('platform.invoices') }}" class="text-sm text-primary font-semibold hover:underline mt-3 inline-block">Kelola tagihan →</a>
        </div>
    </div>
</div>
