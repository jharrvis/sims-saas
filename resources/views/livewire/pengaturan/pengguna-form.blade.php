<div class="max-w-3xl mx-auto">
    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if ($error)
        <div class="mb-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm" role="alert">{{ $error }}</div>
    @endif

    @if ($identitasGlobal)
        <div class="mb-4 rounded-lg bg-warning/10 text-warning px-4 py-2.5 text-xs leading-relaxed" role="status">
            Pengguna ini juga terdaftar di sekolah lain. Perubahan <strong>nama, username, kontak, dan kata sandi</strong>
            berlaku untuk semua sekolah; status &amp; peran di bawah hanya untuk sekolah ini.
        </div>
    @endif

    <form wire:submit="simpan" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-6">
        <div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Identitas Pengguna</h2>
            <div class="grid sm:grid-cols-2 gap-5 mt-4">
                <div>
                    <label for="name" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-danger">*</span></label>
                    <input id="name" type="text" wire:model="name" required autocomplete="name"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="username" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Username <span class="text-danger">*</span></label>
                    <input id="username" type="text" wire:model="username" required autocomplete="username"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white font-mono">
                    <p class="mt-1 text-xs text-slate-500">Dipakai untuk login username + kata sandi. Tanpa spasi.</p>
                    @error('username') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Kontak &amp; Cara Masuk</h2>
            <p class="text-xs text-slate-500 mt-1">Isi minimal salah satu: nomor WhatsApp (OTP) atau kata sandi.</p>
            <div class="grid sm:grid-cols-2 gap-5 mt-4">
                <div>
                    <label for="phone" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nomor WhatsApp</label>
                    <input id="phone" type="tel" wire:model="phone" inputmode="tel" placeholder="0812 3456 7890" autocomplete="tel"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    <p class="mt-1 text-xs text-slate-500">Format 08xx otomatis diubah menjadi 62xx.</p>
                    @error('phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $user ? 'Kata Sandi Baru' : 'Kata Sandi' }}
                    </label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    <p class="mt-1 text-xs text-slate-500">{{ $user ? 'Kosongkan bila tidak ingin mengubah.' : 'Opsional. Minimal 8 karakter.' }}</p>
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Ulangi Kata Sandi</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Peran &amp; Status</h2>
            <p class="text-xs text-slate-500 mt-1">Peran menentukan menu &amp; hak akses pengguna di sekolah ini.</p>

            <div class="grid sm:grid-cols-2 gap-2 mt-4">
                @foreach ($rolesTersedia as $role)
                    <label wire:key="peran-{{ $role->id }}"
                           class="flex items-start gap-3 rounded-lg border border-slate-200 dark:border-slate-700 px-3.5 py-3 cursor-pointer hover:border-primary/50 transition-colors">
                        <input type="checkbox" wire:model="roles" value="{{ $role->name }}" class="mt-0.5 rounded">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-800 dark:text-white">{{ \App\Support\RoleCatalog::label($role->name) }}</span>
                            <span class="block text-xs text-slate-500 leading-snug">{{ \App\Support\RoleCatalog::description($role->name) }}</span>
                            @unless (\App\Support\RoleCatalog::isSystem($role->name))
                                <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-500/10 text-slate-500">Kustom</span>
                            @endunless
                        </span>
                    </label>
                @endforeach
            </div>
            @error('roles') <p class="mt-2 text-xs text-danger">{{ $message }}</p> @enderror
            @error('roles.*') <p class="mt-2 text-xs text-danger">{{ $message }}</p> @enderror

            <div class="mt-5 sm:max-w-xs">
                <label for="status" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Status di sekolah ini</label>
                <select id="status" wire:model="status"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="aktif">Aktif — dapat masuk</option>
                    <option value="dinonaktifkan">Nonaktif — akses ditutup</option>
                </select>
                @error('status') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                @if ($isSelf)
                    <p class="mt-1 text-xs text-slate-500">Anda tidak dapat menonaktifkan akun sendiri.</p>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
            <a href="{{ route('pengaturan.pengguna') }}"
               class="rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Batal
            </a>
            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors disabled:opacity-60">
                <x-icon name="check" class="w-4 h-4" /> {{ $user ? 'Simpan Perubahan' : 'Buat Pengguna' }}
            </button>
        </div>
    </form>
</div>
