<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / Nilai</span>
            <h2 class="management-page-title">Input Nilai</h2>
            <p>Pilih kelas, mata pelajaran, komponen, dan semester, lalu isi nilai tiap siswa.</p>
        </div>
    </div>

    @if (session('saved'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('saved') }}</span>
        </div>
    @endif

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="nilai-rombel"><span class="sr-only">Pilih kelas</span>
                <select id="nilai-rombel" wire:model.live="rombelId" class="select-field">
                    <option value="">— Kelas —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="nilai-mapel"><span class="sr-only">Pilih mata pelajaran</span>
                <select id="nilai-mapel" wire:model.live="subjectId" class="select-field">
                    <option value="">— Mata pelajaran —</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="nilai-komponen"><span class="sr-only">Pilih komponen</span>
                <select id="nilai-komponen" wire:model.live="componentId" class="select-field">
                    <option value="">— Komponen —</option>
                    @foreach ($components as $c)
                        <option value="{{ $c->id }}">{{ $c->nama }} ({{ $c->bobot }}%)</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="nilai-semester"><span class="sr-only">Pilih semester</span>
                <select id="nilai-semester" wire:model.live="semesterId" class="select-field">
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $sem->label() }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if ($rombelId && $subjectId && $componentId && $students->isNotEmpty())
        <form wire:submit="save">
            <div class="card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col" class="w-40">Nilai (0–100)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $s)
                                <tr wire:key="nilai-{{ $s->id }}">
                                    <td>
                                        <div class="font-medium">{{ $s->nama }}</div>
                                        <div class="text-xs text-slate-500">NIS {{ $s->nis }}</div>
                                    </td>
                                    <td>
                                        <input type="number" wire:model="scores.{{ $s->id }}" min="0" max="100" step="0.01"
                                               aria-label="Nilai {{ $s->nama }}" placeholder="—"
                                               class="field">
                                        @error("scores.{$s->id}") <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan Nilai</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    @elseif ($rombelId && $subjectId)
        <div class="empty-state">
            <p>Pilih komponen penilaian atau tidak ada siswa aktif di kelas ini.</p>
        </div>
    @endif
</div>
