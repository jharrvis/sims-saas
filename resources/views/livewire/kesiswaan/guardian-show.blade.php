<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite" data-flash-alert>
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @php
        $namaParts = preg_split('/\s+/', trim($guardian->nama));
        $inisial = strtoupper(mb_substr($namaParts[0], 0, 1).(count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : ''));
        $labelHubungan = match ($guardian->hubungan) { 'ayah' => 'Ayah', 'ibu' => 'Ibu', default => 'Wali' };
        $nikMasked = $guardian->nik ? '••••'.mb_substr($guardian->nik, -4) : '—';
    @endphp

    <div class="page-header management-page-header">
        <div class="management-detail-identity">
            <span class="management-detail-photo-fallback" aria-hidden="true">{{ $inisial }}</span>
            <div>
                <a href="{{ route('wali.index') }}" class="management-back-link">
                    <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Direktori Wali
                </a>
                <span class="eyebrow">Kesiswaan / Data Induk</span>
                <h2 class="management-page-title">{{ $guardian->nama }}</h2>
                <p>
                    {{ $labelHubungan }} · WA {{ $guardian->no_wa }}
                    @if ($guardian->pekerjaan) · {{ $guardian->pekerjaan }}@endif
                </p>
            </div>
        </div>
        <div class="page-actions">
            @can('wali-murid.update')
                <a href="{{ route('wali.edit', $guardian) }}" class="btn btn-outline">
                    <x-icon name="pencil" class="h-4 w-4" /> Ubah Data
                </a>
            @endcan
        </div>
    </div>

    <section class="card" aria-labelledby="guardian-identity-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="contact-round" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="guardian-identity-title">Identitas &amp; Kontak</h3>
                    <p>Data utama dan nomor yang dapat dihubungi.</p>
                </div>
            </div>
        </div>

        <dl class="management-definition-list">
            <div><dt>Nama Lengkap</dt><dd>{{ $guardian->nama }}</dd></div>
            <div><dt>Hubungan Utama</dt><dd>{{ $labelHubungan }}</dd></div>
            <div><dt>NIK</dt><dd>{{ $nikMasked }}</dd></div>
            <div><dt>Pekerjaan</dt><dd>{{ $guardian->pekerjaan ?? '—' }}</dd></div>
            <div><dt>Nomor WA</dt><dd>{{ $guardian->no_wa }}</dd></div>
            <div><dt>Email</dt><dd>{{ $guardian->email ?? '—' }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $guardian->alamat ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="card" aria-labelledby="guardian-students-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="guardian-students-title">Siswa Tertaut</h3>
                    <p>{{ number_format($students->count(), 0, ',', '.') }} siswa tertaut dengan wali ini.</p>
                </div>
            </div>
        </div>

        @if ($students->isNotEmpty())
            <div class="table-scroll">
                <table class="data-table management-data-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama Siswa</th>
                            <th scope="col">NIS</th>
                            <th scope="col">Hubungan</th>
                            <th scope="col" class="management-actions-heading">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $siswa)
                            @php
                                $labelPivot = match ($siswa->pivot->hubungan) { 'ayah' => 'Ayah', 'ibu' => 'Ibu', default => 'Wali' };
                            @endphp
                            <tr wire:key="guardian-student-{{ $siswa->id }}">
                                <td><strong>{{ $siswa->nama }}</strong></td>
                                <td><span class="management-reference">{{ $siswa->nis }}</span></td>
                                <td>{{ $labelPivot }}</td>
                                <td>
                                    <div class="row-actions management-row-actions">
                                        @can('wali-murid.view')<a href="{{ route('siswa.show', $siswa) }}" class="table-action" aria-label="Lihat {{ $siswa->nama }}" title="Lihat detail siswa"><x-icon name="eye" class="h-4 w-4" /></a>@endcan
                                        @can('wali-murid.link')<button type="button" wire:click="unlinkStudent({{ $siswa->id }})" data-sims-confirm="Lepas tautan {{ $siswa->nama }} dari wali ini?" wire:loading.attr="disabled" wire:target="unlinkStudent({{ $siswa->id }})" class="table-action danger" aria-label="Lepas {{ $siswa->nama }}" title="Lepas tautan"><x-icon name="x" class="h-4 w-4" /></button>@endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-symbol"><x-icon name="users" class="h-7 w-7" /></div>
                <h3>Belum ada siswa tertaut</h3>
                <p>Tautkan siswa di bawah supaya wali ini muncul di detail siswa.</p>
            </div>
        @endif

        @can('wali-murid.link')
            <form wire:submit="linkStudent" class="management-form-stack">
                <div class="form-grid management-form-grid">
                    <div class="form-group">
                        <label for="link-search">Cari Siswa</label>
                        <input id="link-search" type="search" wire:model.live.debounce.300ms="linkSearch" class="field" placeholder="Ketik nama atau NIS untuk mempersempit daftar...">
                    </div>
                    <div class="form-group">
                        <label for="link-student">Siswa</label>
                        <select id="link-student" wire:model="linkStudentId" class="select-field">
                            <option value="">Pilih siswa...</option>
                            @foreach ($unlinkedStudents as $calon)
                                <option value="{{ $calon->id }}">{{ $calon->nama }} ({{ $calon->nis }})</option>
                            @endforeach
                        </select>
                        @error('linkStudentId') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label for="link-hubungan">Hubungan dengan Siswa Ini</label>
                        <select id="link-hubungan" wire:model="linkHubungan" class="select-field">
                            <option value="ayah">Ayah</option>
                            <option value="ibu">Ibu</option>
                            <option value="wali">Wali</option>
                        </select>
                        @error('linkHubungan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="page-actions management-form-actions">
                    <button type="submit" wire:loading.attr="disabled" wire:target="linkStudent" class="btn btn-primary btn-sm">
                        <span wire:loading.remove wire:target="linkStudent"><x-icon name="plus" class="h-4 w-4" /> Tautkan Siswa</span>
                        <span wire:loading wire:target="linkStudent">Menautkan...</span>
                    </button>
                </div>
            </form>
        @endcan
    </section>
</div>
