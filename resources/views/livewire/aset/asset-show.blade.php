<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('aset.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Data Aset
            </a>
            <span class="eyebrow">Operasional / Inventaris</span>
            <h2 class="management-page-title">{{ $asset->nama }}</h2>
            <p>
                {{ $asset->kode }} · {{ $asset->category->nama }}
                @if ($asset->lokasi) · Lokasi: {{ $asset->lokasi }} @endif
                · Kondisi: <strong>{{ \App\Models\Asset::KONDISI[$asset->kondisi] }}</strong>
                @if ($asset->harga_beli) · Rp {{ number_format((float) $asset->harga_beli, 0, ',', '.') }} @endif
            </p>
        </div>
        <div class="page-actions">
            @can('aset.mutasi')
                <button wire:click="$set('showForm', true)" class="btn btn-primary">
                    Catat Mutasi
                </button>
            @endcan
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @if ($showForm)
        @can('aset.mutasi')
            <section class="card management-form-card" aria-labelledby="mutasi-form-title">
                <div class="card-head">
                    <div class="card-title">
                        <span class="mini-icon blue"><x-icon name="package" class="h-[19px] w-[19px]" /></span>
                        <div>
                            <h3 id="mutasi-form-title">Catat Mutasi</h3>
                            <p>Pindah lokasi, ubah kondisi, atau barang keluar.</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="simpanMutasi" class="form-grid management-form-grid">
                    <div class="form-group">
                        <label for="mutasi-jenis">Jenis Mutasi</label>
                        <select id="mutasi-jenis" wire:model.live="jenis" class="select-field">
                            @foreach (['pindah' => 'Pindah Lokasi', 'kondisi' => 'Ubah Kondisi', 'keluar' => 'Barang Keluar'] as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mutasi-tgl">Tanggal</label>
                        <input id="mutasi-tgl" type="date" wire:model="tgl" class="field">
                    </div>
                    @if ($jenis === 'pindah')
                        <div class="form-group">
                            <label for="mutasi-dari">Dari</label>
                            <input id="mutasi-dari" type="text" wire:model="dari" maxlength="150" value="{{ $asset->lokasi }}" class="field">
                        </div>
                        <div class="form-group">
                            <label for="mutasi-ke">Ke (lokasi baru)</label>
                            <input id="mutasi-ke" type="text" wire:model="ke" maxlength="150" class="field">
                        </div>
                    @elseif ($jenis === 'kondisi')
                        <div class="form-group">
                            <label for="mutasi-kondisi">Kondisi Baru</label>
                            <select id="mutasi-kondisi" wire:model="ke" class="select-field">
                                @foreach (\App\Models\Asset::KONDISI as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="form-group management-field-span">
                        <label for="mutasi-catatan">Catatan</label>
                        <input id="mutasi-catatan" type="text" wire:model="catatan" maxlength="2000" class="field">
                    </div>
                    <div class="form-actions management-field-span">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpanMutasi">
                            <span wire:loading.remove wire:target="simpanMutasi">Simpan</span>
                            <span wire:loading wire:target="simpanMutasi">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="$set('showForm', false)" class="btn btn-outline">Batal</button>
                    </div>
                </form>
            </section>
        @endcan
    @endif

    <section class="card" aria-labelledby="mutasi-riwayat-title">
        <div class="card-head">
            <div class="card-title">
                <span class="mini-icon green"><x-icon name="scroll-text" class="h-[19px] w-[19px]" /></span>
                <div>
                    <h3 id="mutasi-riwayat-title">Riwayat Mutasi</h3>
                    <p>Perpindahan dan perubahan kondisi aset.</p>
                </div>
            </div>
        </div>

        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Jenis</th>
                        <th scope="col">Dari → Ke</th>
                        <th scope="col">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mutations as $m)
                        <tr wire:key="mutasi-{{ $m->id }}">
                            <td class="text-slate-600 dark:text-slate-300">{{ $m->tgl?->format('d M Y') ?? '—' }}</td>
                            <td class="font-semibold">{{ \App\Models\AssetMutation::JENIS[$m->jenis] }}</td>
                            <td class="text-slate-600 dark:text-slate-300">
                                {{ $m->jenis === 'kondisi' && $m->ke ? (\App\Models\Asset::KONDISI[$m->ke] ?? $m->ke) : ($m->dari ?? '—').' → '.($m->ke ?? '—') }}
                            </td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $m->catatan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-500">Belum ada mutasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $mutations->links() }}</div>
</div>
