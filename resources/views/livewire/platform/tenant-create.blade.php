<div class="page-stack">
    <div class="page-header">
        <div>
            <a href="{{ route('platform.tenants') }}" class="platform-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar tenant
            </a>
            <span class="eyebrow">Platform / Provisioning</span>
            <h2 class="platform-page-title">Tambah Tenant Baru</h2>
            <p>Buat workspace sekolah, langganan trial, admin pertama, dan modul inti dalam satu proses terkontrol.</p>
        </div>
    </div>

    <div class="platform-create-layout">
        <form wire:submit="simpan" class="card platform-form-card" aria-labelledby="tenant-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon orange"><x-icon name="school" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="tenant-form-title">Identitas Sekolah</h3>
                        <p>Data dasar untuk workspace tenant</p>
                    </div>
                </div>
                <span class="badge badge-orange">Langkah tunggal</span>
            </div>

            @if ($errors->any())
                <div class="platform-alert platform-alert-danger" role="alert" aria-live="assertive">
                    <x-icon name="alert-triangle" class="h-5 w-5" />
                    <span>Periksa kembali bidang yang ditandai sebelum membuat tenant.</span>
                </div>
            @endif

            <fieldset class="platform-fieldset">
                <legend>Informasi sekolah</legend>
                <div class="form-grid">
                    <div class="form-group platform-form-full">
                        <label for="nama">Nama sekolah <span class="required" aria-hidden="true">*</span></label>
                        <input id="nama" type="text" wire:model="nama" class="field" placeholder="Contoh: SDN 01 Jakarta"
                            autocomplete="organization" required aria-describedby="nama-hint nama-error">
                        <span id="nama-hint" class="helper-text">Gunakan nama resmi sekolah yang akan tampil di workspace.</span>
                        @error('nama') <span id="nama-error" class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="jenjang">Jenjang <span class="required" aria-hidden="true">*</span></label>
                        <select id="jenjang" wire:model="jenjang" class="select-field" required>
                            <option value="">Pilih jenjang</option>
                            @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                        @error('jenjang') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="npsn">NPSN <span class="platform-optional">Opsional</span></label>
                        <input id="npsn" type="text" wire:model="npsn" class="field platform-tabular" placeholder="20100123"
                            inputmode="numeric" autocomplete="off">
                        @error('npsn') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="platform-fieldset">
                <legend>Administrator sekolah</legend>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin_nama">Nama administrator <span class="required" aria-hidden="true">*</span></label>
                        <input id="admin_nama" type="text" wire:model="admin_nama" class="field" placeholder="Nama penanggung jawab"
                            autocomplete="name" required>
                        @error('admin_nama') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="admin_phone">Nomor WhatsApp <span class="required" aria-hidden="true">*</span></label>
                        <input id="admin_phone" type="tel" wire:model="admin_phone" class="field platform-tabular"
                            placeholder="6281234567890" inputmode="tel" autocomplete="tel" required>
                        <span class="helper-text">Awali dengan kode negara 62 tanpa tanda +.</span>
                        @error('admin_phone') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="platform-fieldset">
                <legend>Langganan awal</legend>
                <div class="form-group">
                    <label for="plan_slug">Paket trial <span class="required" aria-hidden="true">*</span></label>
                    <select id="plan_slug" wire:model="plan_slug" class="select-field" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->slug }}">{{ $plan->nama }} — Rp{{ number_format($plan->harga_bulanan, 0, ',', '.') }}/bulan</option>
                        @endforeach
                    </select>
                    <span class="helper-text">Tenant akan memperoleh trial 30 hari sebelum penagihan reguler.</span>
                    @error('plan_slug') <span class="platform-field-error" role="alert">{{ $message }}</span> @enderror
                </div>
            </fieldset>

            <div class="form-actions platform-form-actions">
                <a href="{{ route('platform.tenants') }}" class="btn btn-soft">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled" wire:target="simpan">
                    <span wire:loading.remove wire:target="simpan" class="platform-button-label">
                        <x-icon name="plus" class="h-4 w-4" /> Buat Tenant
                    </span>
                    <span wire:loading.flex wire:target="simpan" class="platform-button-label">
                        <span class="platform-button-spinner" aria-hidden="true"></span> Menyiapkan tenant...
                    </span>
                </button>
            </div>
        </form>

        <aside class="platform-provisioning-aside" aria-label="Ringkasan provisioning">
            <section class="card">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon green"><x-icon name="check-circle" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3>Dibuat Otomatis</h3>
                            <p>Provisioning aman oleh server</p>
                        </div>
                    </div>
                </div>
                <ul class="platform-check-list">
                    <li><x-icon name="check" class="h-4 w-4" /><span>Workspace sekolah dan slug unik</span></li>
                    <li><x-icon name="check" class="h-4 w-4" /><span>Langganan trial selama 30 hari</span></li>
                    <li><x-icon name="check" class="h-4 w-4" /><span>Membership administrator sekolah</span></li>
                    <li><x-icon name="check" class="h-4 w-4" /><span>Aktivasi modul inti sesuai paket</span></li>
                    <li><x-icon name="check" class="h-4 w-4" /><span>Catatan audit proses provisioning</span></li>
                </ul>
            </section>

            <section class="card platform-note-card">
                <span class="mini-icon orange"><x-icon name="circle-help" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3>Satu nomor, banyak peran</h3>
                    <p>Jika nomor WhatsApp sudah terdaftar, sistem tidak membuat pengguna ganda. Membership sekolah baru ditambahkan ke identitas yang sama.</p>
                </div>
            </section>
        </aside>
    </div>
</div>
