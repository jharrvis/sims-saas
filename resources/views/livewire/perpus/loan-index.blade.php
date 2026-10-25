<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <a href="{{ route('perpus.index') }}" class="management-back-link">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Katalog
            </a>
            <span class="eyebrow">Operasional / Perpustakaan</span>
            <h2 class="management-page-title">Peminjaman Buku</h2>
            <p>Catat peminjaman baru dan kelola pengembalian beserta dendanya.</p>
        </div>
    </div>

    @if (session('ok'))
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    @can('perpus.pinjam')
        <section class="card management-form-card" aria-labelledby="pinjam-form-title">
            <div class="card-head">
                <div class="card-title">
                    <span class="mini-icon blue"><x-icon name="library" class="h-[19px] w-[19px]" /></span>
                    <div>
                        <h3 id="pinjam-form-title">Catat Peminjaman</h3>
                        <p>Cari buku dan siswa, lalu tentukan durasi pinjam.</p>
                    </div>
                </div>
            </div>

            <form wire:submit="pinjam" class="form-grid management-form-grid">
                <div class="form-group">
                    <label for="buku">Buku <span class="required">*</span></label>
                    <input id="buku" type="search" wire:model.live.debounce.300ms="searchBuku" placeholder="Cari judul…" class="field">
                    <select wire:model="book_id" aria-label="Pilih buku" class="select-field mt-1">
                        <option value="">— Pilih buku —</option>
                        @foreach ($bukuList as $b)
                            <option value="{{ $b->id }}">{{ $b->judul }}</option>
                        @endforeach
                    </select>
                    @error('book_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="siswa">Siswa <span class="required">*</span></label>
                    <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…" class="field">
                    <select wire:model="student_id" aria-label="Pilih siswa" class="select-field mt-1">
                        <option value="">— Pilih siswa —</option>
                        @foreach ($siswaList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label for="durasi">Durasi (hari)</label>
                    <input id="durasi" type="number" wire:model="durasiHari" min="1" max="60" class="field">
                    @error('durasiHari') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="form-actions management-field-span">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="pinjam">
                        <span wire:loading.remove wire:target="pinjam">Catat Pinjam</span>
                        <span wire:loading wire:target="pinjam">Mencatat...</span>
                    </button>
                </div>
            </form>
        </section>
    @endcan

    <div class="table-toolbar management-toolbar">
        <div class="toolbar-left management-filter-group">
            <label class="management-filter-field" for="pinjam-status"><span class="sr-only">Filter status</span>
                <select id="pinjam-status" wire:model.live="status" class="select-field">
                    <option value="">Semua</option>
                    @foreach (\App\Models\BookLoan::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Buku</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Jatuh Tempo</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr wire:key="loan-{{ $loan->id }}">
                            <td class="font-semibold">{{ $loan->book->judul }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $loan->student->nama }}</td>
                            <td class="text-slate-600 dark:text-slate-300">{{ $loan->tgl_jatuh_tempo->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $loan->status === 'dipinjam' ? 'badge-orange' : ($loan->status === 'terlambat' ? 'badge-red' : 'badge-green') }}">
                                    {{ \App\Models\BookLoan::STATUS[$loan->status] }}
                                </span>
                                @if ($loan->denda > 0)
                                    <span class="block text-[11px] text-danger mt-0.5">Denda Rp {{ number_format($loan->denda, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($loan->status === 'dipinjam')
                                    @can('perpus.kembali')
                                        <button wire:click="kembali({{ $loan->id }})" class="text-xs font-semibold text-success hover:underline">Tandai Kembali</button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">Tidak ada peminjaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $loans->links() }}</div>
</div>
