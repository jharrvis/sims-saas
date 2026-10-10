<div class="max-w-3xl">
    @include('livewire.pengaturan._tabs')
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="nama" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nama Sekolah <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" maxlength="150"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="npsn" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">NPSN</label>
                <input id="npsn" type="text" wire:model="npsn" maxlength="20"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('npsn') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jenjang" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Jenjang <span class="text-danger">*</span></label>
                <select id="jenjang" wire:model="jenjang"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                    @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="alamat" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Alamat</label>
                <textarea id="alamat" wire:model="alamat" rows="2"
                          class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
            </div>
            <div>
                <label for="telepon" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Telepon</label>
                <input id="telepon" type="text" wire:model="telepon" maxlength="30"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Email</label>
                <input id="email" type="email" wire:model="email" maxlength="100"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                @error('email') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        @can('sekolah.update')
            <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                Simpan Profil
            </button>
        @endcan
    </form>
</div>
