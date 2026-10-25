<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Akademik / E-Rapor</span>
            <h2 class="management-page-title">Daftar Rapor</h2>
            <p>Pilih rombel dan semester untuk menampilkan daftar rapor siswa.</p>
        </div>
    </div>

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="rapor-rombel"><span class="sr-only">Pilih rombel</span>
                <select id="rapor-rombel" wire:model.live="rombelId" class="select-field">
                    <option value="">— Pilih rombel —</option>
                    @foreach ($rombels as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>
            </label>
            <label class="management-filter-field" for="rapor-semester"><span class="sr-only">Pilih semester</span>
                <select id="rapor-semester" wire:model.live="semesterId" class="select-field">
                    <option value="">— Pilih semester —</option>
                    @foreach ($semesters as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if ($rombelId && $semesterId)
        <div class="card">
            <div class="table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">NIS</th>
                            <th scope="col">Nama</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $s)
                            <tr wire:key="rapor-{{ $s->id }}">
                                <td class="text-slate-600 dark:text-slate-300">{{ $s->nis }}</td>
                                <td class="font-semibold">{{ $s->nama }}</td>
                                <td class="text-right">
                                    <a href="{{ route('erapor.show', ['student' => $s->id, 'semester' => $semesterId]) }}"
                                       class="text-xs font-semibold text-primary hover:underline">Lihat Rapor</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-slate-500">Tidak ada siswa aktif di rombel ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="empty-state">
            <p>Pilih rombel dan semester untuk menampilkan daftar rapor.</p>
        </div>
    @endif
</div>
