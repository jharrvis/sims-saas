<div class="max-w-3xl mx-auto">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nis" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NIS <span class="text-danger">*</span></label>
                <input id="nis" type="text" wire:model="nis" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nis') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nisn" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NISN</label>
                <input id="nisn" type="text" wire:model="nisn"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nisn') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="nama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-danger">*</span></label>
            <input id="nama" type="text" wire:model="nama" required
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
        </div>

        <div class="grid sm:grid-cols-3 gap-5">
            <div>
                <label for="jk" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jenis Kelamin <span class="text-danger">*</span></label>
                <select id="jk" wire:model="jk" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div>
                <label for="tempat_lahir" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tempat Lahir</label>
                <input id="tempat_lahir" type="text" wire:model="tempat_lahir"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <div>
                <label for="tgl_lahir" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tanggal Lahir</label>
                <input id="tgl_lahir" type="date" wire:model="tgl_lahir"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="agama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Agama</label>
                <select id="agama" wire:model="agama"
                        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    <option value="">— Pilih Agama —</option>
                    @foreach (\App\Models\Student::AGAMAS as $pilihanAgama)
                        <option value="{{ $pilihanAgama }}">{{ $pilihanAgama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="phone" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">No. HP</label>
                <input id="phone" type="tel" wire:model="phone"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div>
            <label for="alamat" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Alamat</label>
            <textarea id="alamat" wire:model="alamat" rows="3"
                      class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white"></textarea>
        </div>

        <div>
            <span class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Foto Siswa</span>
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 px-4 py-5 text-center transition-colors dark:border-slate-600 dark:bg-slate-800/30"
                 ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
                 ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
                 ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); const f = event.dataTransfer.files[0]; if (f) { const dt = new DataTransfer(); dt.items.add(f); const inp = document.getElementById('foto-input'); inp.files = dt.files; inp.dispatchEvent(new Event('change', {bubbles: true})); }"
                 onclick="document.getElementById('foto-input').click()">
                <div class="mx-auto mb-2 h-20 w-20 shrink-0 overflow-hidden rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl font-bold">
                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto" class="h-full w-full object-cover">
                    @elseif ($student?->fotoUrl())
                        <img src="{{ $student->fotoUrl() }}" alt="Foto {{ $student->nama }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($nama ?: '?', 0, 1)) }}
                    @endif
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">Seret &amp; letakkan foto di sini, atau <span class="font-medium text-primary underline">pilih file</span></p>
                <p class="mt-1 text-xs text-slate-500">JPG / PNG / WebP, maks 2 MB. Foto otomatis dikompresi (maks 512×512, JPEG) agar ringan.</p>
                @error('foto') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <input type="file" id="foto-input" wire:model="foto" accept="image/jpeg,image/png,image/webp" class="hidden">
            @if ($student?->foto_path)
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" wire:model="hapusFoto" class="rounded"> Hapus foto saat ini (kembali ke avatar inisial)
                </label>
            @endif
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan
            </button>
        </div>
    </form>
</div>
