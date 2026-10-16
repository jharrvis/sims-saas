<div class="max-w-3xl mx-auto">
    <form wire:submit="save" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-6 space-y-6">
        <div>
            <span class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Foto Guru</span>
            <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 px-4 py-5 text-center transition-colors dark:border-slate-600 dark:bg-slate-800/30"
                 ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
                 ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
                 ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); const f = event.dataTransfer.files[0]; if (f) { const dt = new DataTransfer(); dt.items.add(f); const inp = document.getElementById('foto-input'); inp.files = dt.files; inp.dispatchEvent(new Event('change', {bubbles: true})); }"
                 onclick="document.getElementById('foto-input').click()">
                <div class="mx-auto mb-2 h-20 w-20 shrink-0 overflow-hidden rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl font-bold">
                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" alt="Pratinjau foto" class="h-full w-full object-cover">
                    @elseif ($employee?->fotoUrl())
                        <img src="{{ $employee->fotoUrl() }}" alt="Foto {{ $employee->nama }}" class="h-full w-full object-cover">
                    @else
                        @php
                            $namaParts = preg_split('/\s+/', trim($nama ?: ''));
                            $inisial = !empty($namaParts[0]) ? strtoupper(mb_substr($namaParts[0], 0, 1) . (count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : '')) : '?';
                        @endphp
                        {{ $inisial }}
                    @endif
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">Seret &amp; letakkan foto di sini, atau <span class="font-medium text-primary underline">pilih file</span></p>
                <p class="mt-1 text-xs text-slate-500">JPG / PNG / WebP, maks 2 MB. Foto otomatis dikompresi (maks 512×512, JPEG) agar ringan.</p>
                @error('foto') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <input type="file" id="foto-input" wire:model="foto" accept="image/jpeg,image/png,image/webp" class="hidden">
            @if ($employee?->foto_path)
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" wire:model="hapusFoto" class="rounded"> Hapus foto saat ini (kembali ke avatar inisial)
                </label>
            @endif
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label for="nama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-danger">*</span></label>
                <input id="nama" type="text" wire:model="nama" required
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nip_nuptk" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NIP / NUPTK</label>
                <input id="nip_nuptk" type="text" wire:model="nip_nuptk"
                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-slate-800 dark:text-white mb-3">Identitas &amp; Kontak</h3>
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="nik" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">NIK</label>
                    <input id="nik" type="text" wire:model="nik" inputmode="numeric"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="jk" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jenis Kelamin</label>
                    <select id="jk" wire:model="jk" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="">—</option>
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
                <div>
                    <label for="agama" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Agama</label>
                    <select id="agama" wire:model="agama" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="">—</option>
                        @foreach (\App\Models\Student::AGAMAS as $agamaOption)
                            <option value="{{ $agamaOption }}">{{ $agamaOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="email" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                    <input id="email" type="email" wire:model="email"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">No. HP</label>
                    <input id="phone" type="tel" wire:model="phone" placeholder="cth. 6281234567890"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="alamat" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Alamat</label>
                    <textarea id="alamat" wire:model="alamat" rows="2"
                              class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white"></textarea>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-slate-800 dark:text-white mb-3">Pendidikan</h3>
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="pendidikan_terakhir" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Pendidikan Terakhir</label>
                    <select id="pendidikan_terakhir" wire:model="pendidikan_terakhir" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::PENDIDIKAN as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tahun_lulus" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Tahun Lulus</label>
                    <input id="tahun_lulus" type="number" wire:model="tahun_lulus" min="1950" max="{{ date('Y') }}" placeholder="cth. 2015"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                    @error('tahun_lulus') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jurusan" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jurusan</label>
                    <input id="jurusan" type="text" wire:model="jurusan" placeholder="cth. Pendidikan Matematika"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="perguruan_tinggi" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Perguruan Tinggi / Sekolah</label>
                    <input id="perguruan_tinggi" type="text" wire:model="perguruan_tinggi"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-slate-800 dark:text-white mb-3">Kepegawaian</h3>
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="jenis" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jenis <span class="text-danger">*</span></label>
                    <select id="jenis" wire:model="jenis" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="guru_mapel">Guru Mapel</option>
                        <option value="guru_kelas">Guru Kelas</option>
                        <option value="tendik">Tendik</option>
                    </select>
                </div>
                <div>
                    <label for="status_kepegawaian" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Status Kepegawaian <span class="text-danger">*</span></label>
                    <select id="status_kepegawaian" wire:model="status_kepegawaian" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="pns">PNS</option>
                        <option value="pppk">PPPK</option>
                        <option value="honorer">Honorer</option>
                        <option value="kontrak">Kontrak</option>
                    </select>
                </div>
                <div>
                    <label for="tmt" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">TMT (Terhitung Mulai Tanggal)</label>
                    <input id="tmt" type="date" wire:model="tmt"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="golongan" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Golongan / Pangkat</label>
                    <select id="golongan" wire:model="golongan" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::GOLONGAN as $gol)
                            <option value="{{ $gol }}">{{ $gol }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sertifikasi" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Sertifikasi</label>
                    <select id="sertifikasi" wire:model="sertifikasi" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white dark:bg-boxdark">
                        <option value="">—</option>
                        @foreach (\App\Models\Employee::SERTIFIKASI as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="no_sertifikat" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">No. Sertifikat Pendidik</label>
                    <input id="no_sertifikat" type="text" wire:model="no_sertifikat"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div>
                    <label for="jabatan" class="block mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Jabatan</label>
                    <input id="jabatan" type="text" wire:model="jabatan" placeholder="cth. Kepala Sekolah"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-transparent py-2.5 px-4 text-sm outline-none focus:border-primary dark:text-white">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="status_aktif" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Status aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('guru.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 rounded-lg bg-primary text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Simpan
            </button>
        </div>
    </form>
</div>
