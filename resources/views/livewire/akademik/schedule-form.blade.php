<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('jadwal.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Jadwal
            </a>
            <span class="eyebrow">Akademik / Jadwal</span>
            <h2 class="management-page-title">Tambah Jadwal</h2>
            <p>Atur kelas, hari, jam, mata pelajaran, dan guru. Bentrok jadwal akan diperingatkan sebelum disimpan.</p>
        </div>
    </div>

    @if ($conflicts !== [])
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4" role="alert">
            <p class="font-semibold text-danger text-sm mb-2">Jadwal bentrok:</p>
            <ul class="list-disc list-inside text-sm text-danger space-y-1">
                @foreach ($conflicts as $c)
                    <li>{{ $c }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="management-form-stack">
        <section class="card management-form-card" aria-labelledby="schedule-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="calendar-days" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="schedule-form-title">Detail Jadwal</h3>
                        <p>Kelas, hari, dan jam mengajar.</p>
                    </div>
                </div>
            </div>

            <div class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="rombel_id">Kelas <span class="required">*</span></label>
                    <select id="rombel_id" wire:model="rombel_id" class="select-field">
                        @foreach ($rombels as $r)
                            <option value="{{ $r->id }}">{{ $r->nama }}</option>
                        @endforeach
                    </select>
                    @error('rombel_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="hari">Hari <span class="required">*</span></label>
                    <select id="hari" wire:model="hari" class="select-field">
                        @foreach (\App\Models\Schedule::HARIS as $h)
                            <option value="{{ $h }}">{{ ucfirst($h) }}</option>
                        @endforeach
                    </select>
                    @error('hari') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="jam_ke">Jam ke <span class="required">*</span></label>
                    <input id="jam_ke" type="number" wire:model="jam_ke" min="1" max="15" required class="field">
                    @error('jam_ke') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="ruang">Ruang</label>
                    <input id="ruang" type="text" wire:model="ruang" placeholder="cth. R-101" class="field">
                    @error('ruang') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="subject_id">Mata Pelajaran <span class="required">*</span></label>
                    <select id="subject_id" wire:model="subject_id" class="select-field">
                        <option value="">— Pilih mapel —</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="employee_id">Guru <span class="required">*</span></label>
                    <select id="employee_id" wire:model="employee_id" class="select-field">
                        <option value="">— Pilih guru —</option>
                        @foreach ($gurus as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('jadwal.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="h-4 w-4" /> Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </section>
    </form>
</div>
