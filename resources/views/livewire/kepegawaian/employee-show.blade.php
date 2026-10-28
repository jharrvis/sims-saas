<div class="page-stack">
    @if (session('sukses'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <x-icon name="check-circle" class="h-5 w-5" />
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @php
        $namaParts = preg_split('/\s+/', trim($employee->nama));
        $inisial = strtoupper(mb_substr($namaParts[0], 0, 1).(count($namaParts) > 1 ? mb_substr(end($namaParts), 0, 1) : ''));
        $badgeStatus = $employee->status_aktif ? 'badge-green' : 'badge-gray';
        $labelStatus = $employee->status_aktif ? 'Aktif' : 'Nonaktif';
    @endphp

    <div class="page-header management-page-header">
        <div class="management-detail-identity">
            @if ($employee->fotoUrl())
                <img src="{{ $employee->fotoUrl() }}" alt="Foto {{ $employee->nama }}" class="management-detail-photo">
            @else
                <span class="management-detail-photo-fallback" aria-hidden="true">{{ $inisial }}</span>
            @endif
            <div>
                <a href="{{ route('guru.index') }}" class="management-back-link">
                    <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Direktori Guru
                </a>
                <span class="eyebrow">Kepegawaian / Data Induk</span>
                <h2 class="management-page-title">{{ $employee->nama }}</h2>
                <p>
                    {{ $employee->labelJenis() }}@if ($employee->jabatan) · {{ $employee->jabatan }}@endif · NIP / NUPTK {{ $employee->nip_nuptk ?? '—' }}
                    <span class="badge {{ $badgeStatus }}">{{ $labelStatus }}</span>
                </p>
            </div>
        </div>
        <div class="page-actions">
            @can('guru.update')
                <a href="{{ route('guru.edit', $employee) }}" class="btn btn-outline">
                    <x-icon name="pencil" class="h-4 w-4" /> Ubah Data
                </a>
            @endcan
        </div>
    </div>

    <section class="card" aria-labelledby="employee-identity-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="contact-round" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-identity-title">Identitas &amp; Kontak</h3>
                    <p>Data utama dan nomor yang dapat dihubungi.</p>
                </div>
            </div>
        </div>

        <dl class="management-definition-list">
            <div><dt>NIP / NUPTK</dt><dd>{{ $employee->nip_nuptk ?? '—' }}</dd></div>
            <div><dt>NIK</dt><dd>{{ $employee->nik ?? '—' }}</dd></div>
            <div><dt>Tempat, Tanggal Lahir</dt><dd>{{ $employee->tempat_lahir ?? '—' }}, {{ $employee->tgl_lahir?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt>Jenis Kelamin</dt><dd>{{ $employee->labelJk() }}</dd></div>
            <div><dt>Agama</dt><dd>{{ $employee->agama ?? '—' }}</dd></div>
            <div><dt>Email</dt><dd>{{ $employee->email ?? '—' }}</dd></div>
            <div><dt>No. HP</dt><dd>{{ $employee->phone ?? '—' }}</dd></div>
            <div><dt>Status</dt><dd>{{ $labelStatus }}</dd></div>
            <div><dt>Alamat</dt><dd>{{ $employee->alamat ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="card" aria-labelledby="employee-pendidikan-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon purple"><x-icon name="scroll-text" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-pendidikan-title">Pendidikan</h3>
                    <p>Riwayat pendidikan formal terakhir.</p>
                </div>
            </div>
        </div>
        <dl class="management-definition-list">
            <div><dt>Pendidikan Terakhir</dt><dd>{{ $employee->labelPendidikan() }}</dd></div>
            <div><dt>Tahun Lulus</dt><dd>{{ $employee->tahun_lulus ?? '—' }}</dd></div>
            <div><dt>Jurusan</dt><dd>{{ $employee->jurusan ?? '—' }}</dd></div>
            <div><dt>Perguruan Tinggi / Sekolah</dt><dd>{{ $employee->perguruan_tinggi ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="card" aria-labelledby="employee-kepegawaian-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon green"><x-icon name="briefcase" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-kepegawaian-title">Kepegawaian</h3>
                    <p>Status, jabatan, dan masa kerja.</p>
                </div>
            </div>
        </div>
        <dl class="management-definition-list">
            <div><dt>Jenis</dt><dd>{{ $employee->labelJenis() }}</dd></div>
            <div><dt>Status Kepegawaian</dt><dd>{{ ucfirst($employee->status_kepegawaian) }}</dd></div>
            <div><dt>Jabatan</dt><dd>{{ $employee->jabatan ?? '—' }}</dd></div>
            <div><dt>TMT</dt><dd>{{ $employee->tmt?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt>Golongan / Pangkat</dt><dd>{{ $employee->golongan ?? '—' }}</dd></div>
            <div><dt>Sertifikasi</dt><dd>{{ $employee->labelSertifikasi() }}</dd></div>
            <div><dt>No. Sertifikat Pendidik</dt><dd>{{ $employee->no_sertifikat ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="card" aria-labelledby="employee-akun-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="key-round" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-akun-title">Akun Login</h3>
                    <p>Akun yang dipakai guru/pegawai ini untuk masuk ke aplikasi.</p>
                </div>
            </div>
        </div>

        @if ($akun)
            <dl class="management-definition-list">
                <div><dt>Username</dt><dd class="font-mono">{{ $akun['username'] }}</dd></div>
                <div><dt>Peran</dt><dd>{{ collect($akun['roles'])->map(fn ($role) => \App\Support\RoleCatalog::label($role))->join(', ') ?: '—' }}</dd></div>
                <div><dt>Status di sekolah ini</dt><dd>{{ $akun['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' }}</dd></div>
            </dl>
            @can('pengguna.update')
                <div class="mt-4 flex justify-end">
                    <a href="{{ route('pengaturan.pengguna.edit', $akun['id']) }}" class="btn btn-outline">
                        <x-icon name="pencil" class="h-4 w-4" /> Kelola di Pengguna
                    </a>
                </div>
            @endcan
        @else
            <div class="empty-state">
                <div class="empty-symbol"><x-icon name="key-round" class="h-7 w-7" /></div>
                <h3>Belum ada akun login</h3>
                <p>Guru/pegawai ini belum dapat masuk ke aplikasi. Buatkan akun agar namanya tertaut dengan daftar Pengguna.</p>
            </div>
            @can('pengguna.create')
                <div class="mt-4 flex justify-end">
                    <a href="{{ route('guru.akun', $employee) }}" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" /> Buatkan Akun Login
                    </a>
                </div>
            @endcan
        @endif
    </section>

    <section class="card" aria-labelledby="employee-wali-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon orange"><x-icon name="school" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-wali-title">Wali Kelas</h3>
                    <p>Rombel yang dibina sebagai wali kelas.</p>
                </div>
            </div>
        </div>

        @if ($employee->rombelsAsWali->isEmpty())
            <div class="empty-state">
                <div class="empty-symbol"><x-icon name="school" class="h-7 w-7" /></div>
                <h3>Bukan wali kelas</h3>
                <p>Guru ini belum menjadi wali kelas di rombel manapun.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Kelas</th>
                            <th scope="col">Tingkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employee->rombelsAsWali as $rombel)
                            <tr wire:key="employee-wali-{{ $rombel->id }}">
                                <td><strong>{{ $rombel->nama }}</strong></td>
                                <td>Tingkat {{ $rombel->tingkat }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="card" aria-labelledby="employee-jadwal-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon blue"><x-icon name="calendar-days" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="employee-jadwal-title">Jadwal Mengajar</h3>
                    <p>Diurutkan per hari lalu menurut jam ke-.</p>
                </div>
            </div>
        </div>

        @if ($jadwalPerHari->isEmpty())
            <div class="empty-state">
                <div class="empty-symbol"><x-icon name="calendar-days" class="h-7 w-7" /></div>
                <h3>Belum ada jadwal mengajar</h3>
                <p>Atur penugasan mata pelajaran untuk menampilkan jadwal di sini.</p>
            </div>
        @else
            @foreach ($jadwalPerHari as $hari => $entries)
                <div class="management-card-section">
                    <h4>{{ ucfirst($hari) }}</h4>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th scope="col">Mata Pelajaran</th>
                                    <th scope="col">Kelas</th>
                                    <th scope="col">Jam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entries as $entry)
                                    <tr wire:key="employee-jadwal-{{ $hari }}-{{ $entry->id }}">
                                        <td><strong>{{ $entry->subject?->nama ?? '—' }}</strong></td>
                                        <td>{{ $entry->rombel?->nama ?? '—' }}</td>
                                        <td>Jam ke-{{ $entry->jam_ke }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </section>
</div>
