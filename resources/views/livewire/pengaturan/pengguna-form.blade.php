<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">{{ $user ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h2>
            <p>Identitas, kontak, peran, dan status akses pengguna di sekolah ini.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('pengaturan.pengguna') }}" class="back-link">← Pengguna</a>
        </div>
    </div>

    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if ($error)
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger" role="alert">{{ $error }}</div>
    @endif

    @if ($identitasGlobal)
        <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/10 dark:border-amber-800 px-4 py-3 text-xs leading-relaxed text-amber-700 dark:text-amber-300" role="status">
            Pengguna ini juga terdaftar di sekolah lain. Perubahan <strong>nama, username, kontak, dan kata sandi</strong>
            berlaku untuk semua sekolah; status &amp; peran di bawah hanya untuk sekolah ini.
        </div>
    @endif

    <form wire:submit="simpan" class="card management-form-card" aria-labelledby="pengguna-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="pengguna-form-title">Identitas Pengguna</h3>
                    <p>Nama dan username dipakai untuk masuk dan tampil di seluruh modul.</p>
                </div>
            </div>
        </div>

        <div class="form-grid management-form-grid">
            <div class="form-group">
                <label for="name">Nama Lengkap <span class="required">*</span></label>
                <input id="name" type="text" wire:model="name" required autocomplete="name" class="field">
                @error('name') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input id="username" type="text" wire:model="username" required autocomplete="username" class="field font-mono">
                <p class="mt-1 text-xs text-slate-500">Dipakai untuk login username + kata sandi. Tanpa spasi.</p>
                @error('username') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <h3 class="font-bold mt-4">Kontak &amp; Cara Masuk</h3>
            <p class="text-xs text-slate-500 mt-1">Isi minimal salah satu: nomor WhatsApp (OTP) atau kata sandi.</p>
            <div class="form-grid management-form-grid mt-4">
                <div class="form-group">
                    <label for="phone">Nomor WhatsApp</label>
                    <input id="phone" type="tel" wire:model="phone" inputmode="tel" placeholder="0812 3456 7890" autocomplete="tel" class="field">
                    <p class="mt-1 text-xs text-slate-500">Format 08xx otomatis diubah menjadi 62xx.</p>
                    @error('phone') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" class="field">
                    @error('email') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="password">
                        {{ $user ? 'Kata Sandi Baru' : 'Kata Sandi' }}
                    </label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password" class="field">
                    <p class="mt-1 text-xs text-slate-500">{{ $user ? 'Kosongkan bila tidak ingin mengubah.' : 'Opsional. Minimal 8 karakter.' }}</p>
                    @error('password') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Ulangi Kata Sandi</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="field">
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <h3 class="font-bold mt-4">Peran &amp; Status</h3>
            <p class="text-xs text-slate-500 mt-1">Peran menentukan menu &amp; hak akses pengguna di sekolah ini.</p>

            <div class="grid sm:grid-cols-2 gap-2 mt-4">
                @foreach ($rolesTersedia as $role)
                    <label wire:key="peran-{{ $role->id }}"
                           class="flex items-start gap-3 rounded-lg border border-slate-200 dark:border-slate-700 px-3.5 py-3 cursor-pointer hover:border-primary/50 transition-colors">
                        <input type="checkbox" wire:model="roles" value="{{ $role->name }}" class="mt-0.5 rounded">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium">{{ \App\Support\RoleCatalog::label($role->name) }}</span>
                            <span class="block text-xs text-slate-500 leading-snug">{{ \App\Support\RoleCatalog::description($role->name) }}</span>
                            @unless (\App\Support\RoleCatalog::isSystem($role->name))
                                <span class="badge badge-gray mt-1">Kustom</span>
                            @endunless
                        </span>
                    </label>
                @endforeach
            </div>
            @error('roles') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            @error('roles.*') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror

            <div class="mt-5 sm:max-w-xs">
                <div class="form-group">
                    <label for="status">Status di sekolah ini</label>
                    <select id="status" wire:model="status" class="select-field">
                        <option value="aktif">Aktif — dapat masuk</option>
                        <option value="dinonaktifkan">Nonaktif — akses ditutup</option>
                    </select>
                    @error('status') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    @if ($isSelf)
                        <p class="mt-1 text-xs text-slate-500">Anda tidak dapat menonaktifkan akun sendiri.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <a href="{{ route('pengaturan.pengguna') }}" class="btn btn-outline">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary" wire:target="simpan">
                <x-icon name="check" class="w-4 h-4" />
                <span wire:loading.remove wire:target="simpan">{{ $user ? 'Simpan Perubahan' : 'Buat Pengguna' }}</span>
                <span wire:loading wire:target="simpan">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
