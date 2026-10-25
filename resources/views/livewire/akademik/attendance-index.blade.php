<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Presensi</span>
            <h2 class="management-page-title">Presensi Harian</h2>
            <p>Pilih kelas dan tanggal, tandai status tiap siswa, lalu simpan atau kunci.</p>
        </div>
    </div>

    @if (session('saved'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('saved') }}</span>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="absensi-rombel"><span class="sr-only">Pilih kelas</span>
                <select id="absensi-rombel" wire:model.live="rombelId" class="select-field">
                    <option value="">— Pilih kelas —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="absensi-tanggal"><span class="sr-only">Tanggal</span>
                <input id="absensi-tanggal" type="date" wire:model.live="tanggal" class="field min-h-11">
            </label>
            @if ($locked)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                    <x-icon name="lock" class="h-4 w-4" /> Absensi terkunci
                </span>
            @endif
        </div>
    </div>

    @if ($rombelId && $students->isNotEmpty())
        @can('absensi.create')
            @if (! $locked)
                <div class="flex flex-wrap gap-2">
                    @foreach (['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpa'] as $kode => $label)
                        <button wire:click="markAll('{{ $kode }}')" class="btn btn-outline">
                            Semua {{ $label }}
                        </button>
                    @endforeach
                </div>
            @endif
        @endcan

        <div class="card">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Status</th>
                            <th scope="col">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $s)
                            <tr wire:key="absensi-{{ $s->id }}">
                                <td>
                                    <div class="font-medium">{{ $s->nama }}</div>
                                    <div class="text-xs text-slate-500">NIS {{ $s->nis }}</div>
                                </td>
                                <td>
                                    <div class="flex gap-1" role="radiogroup" aria-label="Status {{ $s->nama }}">
                                        @foreach (['H', 'I', 'S', 'A'] as $kode)
                                            <button wire:click="$set('marks.{{ $s->id }}', '{{ $kode }}')" @disabled($locked)
                                                    aria-pressed="{{ ($marks[$s->id] ?? null) === $kode }}"
                                                    class="w-11 h-11 rounded-lg text-xs font-bold border transition-colors
                                                        {{ ($marks[$s->id] ?? null) === $kode
                                                            ? ($kode === 'H' ? 'bg-success text-white border-success' : ($kode === 'A' ? 'bg-danger text-white border-danger' : 'bg-warning text-white border-warning'))
                                                            : 'border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                                                {{ $kode }}
                                            </button>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <input type="text" wire:model="notes.{{ $s->id }}" @disabled($locked) placeholder="—"
                                           aria-label="Keterangan {{ $s->nama }}"
                                           class="field min-h-11">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @can('absensi.create')
            @if (! $locked)
                <div class="form-actions">
                    @can('absensi.kunci')
                        <button wire:click="lock" wire:confirm="Kunci absensi tanggal ini? Data yang terkunci tidak bisa diubah."
                                class="btn btn-outline">
                            <x-icon name="lock" class="h-4 w-4" /> Kunci
                        </button>
                    @endcan
                    <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">Simpan Absensi</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            @endif
        @endcan
    @elseif ($rombelId)
        <div class="empty-state">
            <p>Tidak ada siswa aktif di kelas ini.</p>
        </div>
    @endif
</div>
