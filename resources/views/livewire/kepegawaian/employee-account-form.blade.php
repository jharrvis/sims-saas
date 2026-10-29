<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('guru.show', $employee) }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Detail Guru
            </a>
            <span class="eyebrow">Kepegawaian / Data Induk</span>
            <h2 class="management-page-title">Buatkan Akun Login</h2>
            <p>Akun untuk {{ $employee->nama }} ({{ $employee->labelJenis() }}) agar dapat masuk ke aplikasi.</p>
        </div>
    </div>

    @if ($error)
        <div class="management-alert management-alert-warning" role="alert">
            <x-icon name="alert-triangle" class="h-5 w-5" />
            <span>{{ $error }}</span>
        </div>
    @endif

    <section class="card management-form-card" aria-labelledby="employee-account-info-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-account-info-title">{{ $employee->nama }}</h3>
                    <p>
                        {{ $employee->labelJenis() }}@if ($employee->jabatan) · {{ $employee->jabatan }}@endif
                        · NIP / NUPTK {{ $employee->nip_nuptk ?? '—' }}
                    </p>
                </div>
            </div>
        </div>
        <p class="helper-text">
            Nama dan kontak di bawah diambil dari data kepegawaian. Mengubah kontak di sini
            tidak mengubah data kepegawaian. Status akun mengikuti status kepegawaian:
            {{ $employee->status_aktif ? 'aktif' : 'nonaktif' }}.
        </p>
    </section>

    <form wire:submit="simpan" class="card management-form-card" aria-labelledby="employee-account-form-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="key-round" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-account-form-title">Kredensial &amp; Peran</h3>
                    <p>Username dipakai untuk login. Isi minimal salah satu: nomor WhatsApp (OTP) atau kata sandi.</p>
                </div>
            </div>
        </div>

        <div class="form-grid management-form-grid">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input id="username" type="text" wire:model="username" required autocomplete="username" class="field font-mono">
                <p class="mt-1 text-xs text-slate-500">Dipakai untuk login username + kata sandi. Tanpa spasi.</p>
                @error('username') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="phone">Nomor WhatsApp</label>
                <input id="phone" type="tel" wire:model="phone" inputmode="tel" placeholder="0812 3456 7890" autocomplete="tel" class="field">
                <p class="mt-1 text-xs text-slate-500">Diambil dari data kepegawaian. Format 08xx otomatis diubah menjadi 62xx.</p>
                @error('phone') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" class="field">
                @error('email') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input id="password" type="password" wire:model="password" autocomplete="new-password" class="field">
                <p class="mt-1 text-xs text-slate-500">Opsional bila nomor WhatsApp diisi. Minimal 8 karakter.</p>
                @error('password') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Ulangi Kata Sandi</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="field">
                @error('password_confirmation') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <h3 class="font-bold mt-4">Peran di sekolah ini</h3>
            <p class="text-xs text-slate-500 mt-1">Peran menentukan menu &amp; hak akses akun ini. Peran bawaan sudah dipilih sesuai jenis kepegawaian.</p>

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
        </div>

        <div class="page-actions management-form-actions">
            <a href="{{ route('guru.show', $employee) }}" class="btn btn-outline">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary" wire:target="simpan">
                <x-icon name="check" class="w-4 h-4" />
                <span wire:loading.remove wire:target="simpan">Buat Akun Login</span>
                <span wire:loading wire:target="simpan">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
