<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @php
        // Palet status selaras daftar siswa (QA F-11).
        $badgeStatus = match ($student->status) { 'aktif' => 'badge-green', 'lulus' => 'badge-blue', 'pindah' => 'badge-orange', 'keluar', 'do' => 'badge-red', default => 'badge-gray' };
        $labelStatus = match ($student->status) { 'aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar', 'do' => 'DO', default => ucfirst($student->status) };
    @endphp

    <div class="page-header management-page-header">
        <div class="management-detail-identity">
            @if ($student->fotoUrl())
                <img src="{{ $student->fotoUrl() }}" alt="Foto {{ $student->nama }}" class="management-detail-photo">
            @else
                <span class="management-detail-photo-fallback" aria-hidden="true">{{ strtoupper(substr($student->nama, 0, 1)) }}</span>
            @endif
            <div>
                <a href="{{ route('siswa.index') }}" class="management-back-link">
                    <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Data Siswa
                </a>
                <span class="eyebrow">Kesiswaan / Data Siswa</span>
                <h2 class="management-page-title">{{ $student->nama }}</h2>
                <p>
                    NIS {{ $student->nis }}@if ($student->nisn) · NISN {{ $student->nisn }}@endif
                    <span class="badge {{ $badgeStatus }}">{{ $labelStatus }}</span>
                </p>
            </div>
        </div>
        <div class="page-actions">
            @can('siswa.view')
                <a href="{{ route('siswa.kartu', $student) }}" class="btn btn-outline">
                    <x-icon name="credit-card" class="h-4 w-4" /> Kartu Pelajar
                </a>
            @endcan
            @can('siswa.update')
                <a href="{{ route('siswa.edit', $student) }}" class="btn btn-outline">
                    <x-icon name="pencil" class="h-4 w-4" /> Ubah
                </a>
            @endcan
        </div>
    </div>

    <section class="card" aria-labelledby="student-identity-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="contact-round" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="student-identity-title">Biodata</h3>
                    <p>Data utama dan nomor yang dapat dihubungi.</p>
                </div>
            </div>
        </div>

        <dl class="management-definition-list">
            <div><dt>Jenis Kelamin</dt><dd>{{ $student->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
            <div><dt>Tempat, Tanggal Lahir</dt><dd>{{ $student->tempat_lahir ?? '—' }}, {{ $student->tgl_lahir?->translatedFormat('d M Y', 'id') ?? '—' }}</dd></div>
            <div><dt>Agama</dt><dd>{{ $student->agama ?? '—' }}</dd></div>
            <div><dt>No. HP</dt><dd>{{ $student->phone ?? '—' }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $student->alamat ?? '—' }}</dd></div>
            <div><dt>Status</dt><dd>{{ $labelStatus }}</dd></div>
        </dl>
    </section>

    <section class="card" aria-labelledby="student-wali-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon orange"><x-icon name="users" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="student-wali-title">Wali Murid</h3>
                    <p>Daftar wali yang tertaut, hanya tampil (read-only).</p>
                </div>
            </div>
        </div>

        @if ($student->guardians->isEmpty())
            <div class="empty-state">
                <p>Belum ada data wali.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Hubungan</th>
                            <th scope="col">No. WA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($student->guardians as $wali)
                            <tr wire:key="student-guardian-{{ $wali->id }}">
                                <td>
                                    @can('wali-murid.view')
                                        <a href="{{ route('wali.show', $wali) }}"><strong>{{ $wali->nama }}</strong></a>
                                    @else
                                        <strong>{{ $wali->nama }}</strong>
                                    @endcan
                                </td>
                                <td>{{ ucfirst($wali->pivot->hubungan) }}</td>
                                <td>{{ $wali->no_wa }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
