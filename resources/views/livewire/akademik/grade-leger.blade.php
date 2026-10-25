<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Nilai</span>
            <h2 class="management-page-title">Leger Nilai</h2>
            <p>Nilai akhir per siswa. Nilai yang sudah dirilis dapat dilihat siswa dan orang tua di PWA.</p>
        </div>
    </div>

    @if (session('released'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('released') }}</span>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="leger-rombel"><span class="sr-only">Pilih kelas</span>
                <select id="leger-rombel" wire:model.live="rombelId" class="select-field">
                    <option value="">— Kelas —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="leger-mapel"><span class="sr-only">Pilih mata pelajaran</span>
                <select id="leger-mapel" wire:model.live="subjectId" class="select-field">
                    <option value="">— Mata pelajaran —</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="leger-semester"><span class="sr-only">Pilih semester</span>
                <select id="leger-semester" wire:model.live="semesterId" class="select-field">
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $sem->label() }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="toolbar-right management-directory-actions">
            @if ($released)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-success">
                    <x-icon name="check-circle" class="h-4 w-4" /> Sudah dirilis
                </span>
            @endif
            @can('nilai.release')
                @if ($rombelId && $semesterId && ! $released)
                    <button wire:click="release" wire:confirm="Rilis nilai kelas ini ke PWA siswa/orang tua?"
                            class="btn btn-success">
                        <x-icon name="send" class="h-4 w-4" /> Rilis Nilai
                    </button>
                @endif
            @endcan
        </div>
    </div>

    @if ($leger->isNotEmpty())
        <div class="card">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">NIS</th>
                            <th scope="col" class="text-right">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leger as $row)
                            <tr wire:key="leger-{{ $row['nis'] }}">
                                <td class="font-medium">{{ $row['nama'] }}</td>
                                <td class="text-slate-500">{{ $row['nis'] }}</td>
                                <td class="text-right">
                                    @if ($row['nilai_akhir'] !== null)
                                        <span class="badge {{ $row['nilai_akhir'] >= 75 ? 'badge-green' : 'badge-red' }}">
                                            {{ number_format($row['nilai_akhir'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($rombelId && $subjectId)
        <div class="empty-state">
            <p>Belum ada data nilai.</p>
        </div>
    @endif
</div>
