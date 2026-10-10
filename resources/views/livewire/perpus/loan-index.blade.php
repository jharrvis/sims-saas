<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="mb-5">
        <a href="{{ route('perpus.index') }}" class="text-sm text-slate-500 hover:text-primary">← Kembali ke Katalog</a>
    </div>

    @can('perpus.pinjam')
        <form wire:submit="pinjam" class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 mb-5">
            <h2 class="font-semibold text-slate-800 dark:text-white mb-4">Catat Peminjaman</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label for="buku" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Buku <span class="text-danger">*</span></label>
                    <input id="buku" type="search" wire:model.live.debounce.300ms="searchBuku" placeholder="Cari judul…"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    <select wire:model="book_id" aria-label="Pilih buku"
                            class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                        <option value="">— Pilih buku —</option>
                        @foreach ($bukuList as $b)
                            <option value="{{ $b->id }}">{{ $b->judul }}</option>
                        @endforeach
                    </select>
                    @error('book_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="siswa" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Siswa <span class="text-danger">*</span></label>
                    <input id="siswa" type="search" wire:model.live.debounce.300ms="searchSiswa" placeholder="Cari nama…"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                    <select wire:model="student_id" aria-label="Pilih siswa"
                            class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3 text-sm dark:text-white">
                        <option value="">— Pilih siswa —</option>
                        @foreach ($siswaList as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} · {{ $s->nis }}</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="durasi" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Durasi (hari)</label>
                    <input id="durasi" type="number" wire:model="durasiHari" min="1" max="60"
                           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white">
                </div>
            </div>
            <button type="submit" class="mt-4 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Catat Pinjam</button>
        </form>
    @endcan

    <div class="flex items-center gap-2 mb-4">
        <select wire:model.live="status" aria-label="Filter status"
                class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
            <option value="">Semua</option>
            @foreach (\App\Models\BookLoan::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Buku</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Siswa</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Jatuh Tempo</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Status</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($loans as $loan)
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-white">{{ $loan->book->judul }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $loan->student->nama }}</td>
                        <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">{{ $loan->tgl_jatuh_tempo->format('d M Y') }}</td>
                        <td class="px-4 py-2.5">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $loan->status === 'dipinjam' ? 'bg-warning/15 text-amber-600' : ($loan->status === 'terlambat' ? 'bg-danger/10 text-danger' : 'bg-success/10 text-success') }}">
                                {{ \App\Models\BookLoan::STATUS[$loan->status] }}
                            </span>
                            @if ($loan->denda > 0)
                                <span class="block text-[11px] text-danger mt-0.5">Denda Rp {{ number_format($loan->denda, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            @if ($loan->status === 'dipinjam')
                                @can('perpus.kembali')
                                    <button wire:click="kembali({{ $loan->id }})" class="text-xs font-semibold text-success hover:underline">Tandai Kembali</button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada peminjaman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $loans->links() }}</div>
</div>
