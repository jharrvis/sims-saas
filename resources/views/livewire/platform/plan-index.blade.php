<div class="page-stack">
    @if (session('ok'))
        <div class="platform-alert platform-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @php
        $activePlans = $plans->where('aktif', true)->count();
        $lowestPrice = $plans->where('aktif', true)->min('harga_bulanan');
    @endphp

    <div class="page-header">
        <div>
            <span class="eyebrow">Platform / Komersial</span>
            <h2 class="platform-page-title">Paket Langganan</h2>
            <p>Susun pilihan paket dan batas siswa yang menjadi dasar langganan setiap sekolah.</p>
        </div>
        @if (auth()->user()?->hasPermissionTo('platform.plan.manage', 'platform'))
            <div class="page-actions">
                <button type="button" wire:click="edit(null)" wire:loading.attr="disabled" wire:target="edit(null)" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Paket Baru
                </button>
            </div>
        @endif
    </div>

    <div class="stats-grid platform-summary-stats">
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon blue"><x-icon name="package" class="h-5 w-5" /></span><span class="badge badge-blue">Katalog</span></div>
            <div class="stat-value">{{ $plans->count() }}</div>
            <div class="stat-caption">Total paket</div>
            <div class="stat-footer"><span class="stat-note">Seluruh paket platform</span></div>
        </article>
        <article class="card stat-card">
            <div class="stat-top"><span class="mini-icon green"><x-icon name="check-circle" class="h-5 w-5" /></span><span class="badge badge-green">Tersedia</span></div>
            <div class="stat-value">{{ $activePlans }}</div>
            <div class="stat-caption">Paket aktif</div>
            <div class="stat-footer"><span class="stat-note">Dapat dipilih tenant</span></div>
        </article>
        <article class="card stat-card platform-wide-stat">
            <div class="stat-top"><span class="mini-icon orange"><x-icon name="wallet" class="h-5 w-5" /></span><span class="badge badge-orange">Bulanan</span></div>
            <div class="stat-value platform-currency-stat">{{ $lowestPrice === null ? '—' : 'Rp'.number_format($lowestPrice, 0, ',', '.') }}</div>
            <div class="stat-caption">Harga aktif terendah</div>
            <div class="stat-footer"><span class="stat-note">Belum termasuk penyesuaian tenant</span></div>
        </article>
    </div>

    <section class="card" aria-labelledby="plan-catalog-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon purple"><x-icon name="package" class="h-[19px] w-[19px]" /></span>
                <div><h3 id="plan-catalog-title">Katalog Paket</h3><p>Harga, kapasitas, dan status penawaran</p></div>
            </div>
        </div>

        <div class="platform-plan-grid">
            @forelse ($plans as $plan)
                <article class="platform-plan-card {{ $plan->aktif ? '' : 'is-inactive' }}" wire:key="platform-plan-{{ $plan->id }}">
                    <div class="platform-plan-head">
                        <div><span class="platform-plan-code">{{ $plan->slug }}</span><h3>{{ $plan->nama }}</h3></div>
                        <span class="badge {{ $plan->aktif ? 'badge-green' : 'badge-gray' }}">{{ $plan->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="platform-plan-price">Rp{{ number_format($plan->harga_bulanan, 0, ',', '.') }}<small>/bulan</small></div>
                    <p class="platform-plan-description">{{ $plan->deskripsi ?: 'Belum ada deskripsi paket.' }}</p>
                    <dl class="platform-plan-facts">
                        <div><dt>Tahunan</dt><dd>{{ $plan->harga_tahunan === null ? '—' : 'Rp'.number_format($plan->harga_tahunan, 0, ',', '.') }}</dd></div>
                        <div><dt>Kapasitas siswa</dt><dd>{{ $plan->max_siswa === null ? 'Tanpa batas' : number_format($plan->max_siswa, 0, ',', '.') }}</dd></div>
                    </dl>
                    @if (auth()->user()?->hasPermissionTo('platform.plan.manage', 'platform'))
                        <button type="button" wire:click="edit({{ $plan->id }})" wire:loading.attr="disabled" wire:target="edit({{ $plan->id }})" class="btn btn-soft btn-sm platform-plan-edit">
                            <x-icon name="pencil" class="h-4 w-4" /> Ubah Paket
                        </button>
                    @endif
                </article>
            @empty
                <div class="empty-state platform-grid-empty">
                    <div class="empty-symbol"><x-icon name="package" class="h-7 w-7" /></div>
                    <h3>Belum ada paket</h3><p>Buat paket pertama untuk mulai mengatur langganan tenant.</p>
                </div>
            @endforelse
        </div>
    </section>

    @if ($showForm && auth()->user()?->hasPermissionTo('platform.plan.manage', 'platform'))
        <section class="card platform-form-card" aria-labelledby="plan-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="edit-3" class="h-[19px] w-[19px]" /></span>
                    <div><h3 id="plan-form-title">{{ $editId ? 'Ubah Paket' : 'Paket Baru' }}</h3><p>Lengkapi identitas, harga, dan kapasitas paket</p></div>
                </div>
                <button type="button" wire:click="closeForm" class="btn btn-soft btn-sm">Tutup</button>
            </div>
            <form wire:submit="simpan">
                <div class="form-grid platform-plan-form-grid">
                    <div class="form-group"><label for="plan-name">Nama paket <span class="required">*</span></label><input id="plan-name" type="text" wire:model="nama" class="field" autocomplete="off">@error('nama') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                    <div class="form-group"><label for="plan-slug">Slug <span class="required">*</span></label><input id="plan-slug" type="text" wire:model="slug" class="field" autocomplete="off" placeholder="contoh: profesional">@error('slug') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                    <div class="form-group"><label for="plan-monthly-price">Harga bulanan (Rp) <span class="required">*</span></label><input id="plan-monthly-price" type="number" wire:model="harga_bulanan" min="0" class="field" inputmode="numeric">@error('harga_bulanan') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                    <div class="form-group"><label for="plan-yearly-price">Harga tahunan (Rp)</label><input id="plan-yearly-price" type="number" wire:model="harga_tahunan" min="0" class="field" inputmode="numeric">@error('harga_tahunan') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                    <div class="form-group"><label for="plan-student-limit">Maksimal siswa</label><input id="plan-student-limit" type="number" wire:model="max_siswa" min="0" class="field" inputmode="numeric" placeholder="Kosong berarti tanpa batas">@error('max_siswa') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                    <div class="form-group platform-plan-status-field"><span class="platform-control-label">Status paket</span><label class="platform-check-control"><input type="checkbox" wire:model="aktif"><span>Aktif dan dapat dipilih tenant</span></label>@error('aktif') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                    <div class="form-group platform-form-full"><label for="plan-description">Deskripsi</label><textarea id="plan-description" wire:model="deskripsi" rows="3" class="textarea-field" maxlength="500"></textarea>@error('deskripsi') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror</div>
                </div>
                <div class="form-actions platform-form-actions">
                    <button type="button" wire:click="closeForm" class="btn btn-outline">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="simpan" class="btn btn-primary"><span wire:loading.remove wire:target="simpan">Simpan Paket</span><span wire:loading wire:target="simpan">Menyimpan...</span></button>
                </div>
            </form>
        </section>
    @endif
</div>
