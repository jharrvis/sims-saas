<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">Profil Sekolah</h2>
            <p>Identitas sekolah yang tampil di dokumen dan komunikasi resmi.</p>
        </div>
    </div>

    @include('livewire.pengaturan._tabs')

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <section class="card management-form-card" aria-labelledby="profil-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="settings" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="profil-form-title">Data Sekolah</h3>
                    <p>Nama, NPSN, jenjang, alamat, dan kontak sekolah.</p>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="form-grid management-form-grid">
            <div class="form-group management-field-span">
                <label for="nama">Nama Sekolah <span class="required">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150" class="field">
                @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="npsn">NPSN</label>
                <input id="npsn" type="text" wire:model="npsn" maxlength="20" class="field">
                @error('npsn') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="jenjang">Jenjang <span class="required">*</span></label>
                <select id="jenjang" wire:model="jenjang" class="select-field">
                    @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
                @error('jenjang') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group management-field-span">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" wire:model="alamat" rows="2" class="field"></textarea>
                @error('alamat') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="telepon">Telepon</label>
                <input id="telepon" type="text" wire:model="telepon" maxlength="30" class="field">
                @error('telepon') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" wire:model="email" maxlength="100" class="field">
                @error('email') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions management-field-span">
                @can('sekolah.update')
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Simpan Profil</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                @endcan
            </div>
        </form>
    </section>
</div>
