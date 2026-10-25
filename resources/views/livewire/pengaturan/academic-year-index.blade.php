<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">Tahun Ajaran</h2>
            <p>Daftar tahun ajaran sekolah beserta periode dan status aktifnya.</p>
        </div>
    </div>

    @include('livewire.pengaturan._tabs')

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @can('tahun-ajaran.create')
        <section class="card management-form-card" aria-labelledby="tahun-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="calendar-days" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="tahun-form-title">Tambah Tahun Ajaran</h3>
                        <p>Tahun ajaran aktif dipakai sebagai periode berjalan sekolah.</p>
                    </div>
                </div>
            </div>

            <form wire:submit="save" class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="ta-nama">Nama</label>
                    <input id="ta-nama" type="text" wire:model="nama" placeholder="2026/2027" maxlength="50" class="field">
                    @error('nama') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="ta-mulai">Mulai</label>
                    <input id="ta-mulai" type="date" wire:model="tgl_mulai" class="field">
                    @error('tgl_mulai') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="ta-selesai">Selesai</label>
                    <input id="ta-selesai" type="date" wire:model="tgl_selesai" class="field">
                    @error('tgl_selesai') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Tambah</span>
                        <span wire:loading wire:target="save">Menambah...</span>
                    </button>
                </div>
            </form>
        </section>
    @endcan

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Tahun Ajaran</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="tahun-{{ $item->id }}">
                            <td class="font-semibold">{{ $item->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $item->tgl_mulai }} – {{ $item->tgl_selesai }}</td>
                            <td>
                                @if ($item->aktif)
                                    <span class="badge badge-green">AKTIF</span>
                                @else
                                    <span class="badge badge-gray">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('tahun-ajaran.activate')
                                    @if (! $item->aktif)
                                        <button wire:click="activate({{ $item->id }})" class="text-xs font-semibold text-primary hover:underline">Aktifkan</button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-500">Belum ada tahun ajaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
