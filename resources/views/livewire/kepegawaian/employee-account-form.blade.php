<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Kepegawaian</span>
            <h2 class="management-page-title">Buatkan Akun Login</h2>
            <p>Akun untuk {{ $employee->nama }} ({{ $employee->labelJenis() }}) agar dapat masuk ke aplikasi.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('guru.show', $employee) }}" class="back-link">← Detail Guru</a>
        </div>
    </div>

    @if ($error)
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger" role="alert">{{ $error }}</div>
    @endif

    <div class="card">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <x-icon name="users" class="w-5 h-5" />
            </div>
            <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                <p class="text-sm font-semibold">{{ $employee->nama }}</p>
                <p class="mt-1">
                    {{ $employee->labelJenis() }}@if ($employee->jabatan) · {{ $employee->jabatan }}@endif
                    · NIP / NUPTK {{ $employee->nip_nuptk ?? '—' }}
                </p>
                <p class="mt-1">
                    Nama dan kontak di bawah diambil dari data kepegawaian. Mengubah kontak di sini
                    tidak mengubah data kepegawaian. Status akun mengikuti status kepegawaian:
                    {{ $employee->status_aktif ? 'aktif' : 'nonaktif' }}.
                </p>
            </div>
        </div>
    </div>

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

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <a href="{{ route('guru.show', $employee) }}" class="btn btn-outline">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary" wire:target="simpan">
                <x-icon name="check" class="w-4 h-4" />
                <span wire:loading.remove wire:target="simpan">Buat Akun Login</span>
                <span wire:loading wire:target="simpan">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
