<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama / NIS…"
                       aria-label="Cari siswa"
                       class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 pl-10 pr-4 text-sm outline-none focus:border-primary dark:text-white">
            </div>
            <select wire:model.live="rombel_id" aria-label="Filter rombel"
                    class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-boxdark py-2.5 px-3 text-sm outline-none focus:border-primary dark:text-white">
                <option value="">Semua rombel</option>
                @foreach ($rombels as $r)
                    <option value="{{ $r->id }}">{{ $r->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            @can('qr.manage')
                @if ($rombel_id !== '')
                    <button wire:click="terbitkanMassal" data-sims-confirm="Terbitkan kartu QR baru untuk semua siswa rombel ini? Kartu lama dinonaktifkan."
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                        <i data-lucide="printer" class="w-4 h-4"></i> Terbitkan & Cetak Rombel
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Siswa</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-600 dark:text-slate-300">Kartu</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $s)
                    @php $card = $s->qrCard; @endphp
                    <tr class="border-t border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-2.5">
                            <span class="block font-semibold text-slate-800 dark:text-white">{{ $s->nama }}</span>
                            <span class="text-xs text-slate-400">NIS {{ $s->nis }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($card && $card->isValid())
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-success/10 text-success">Aktif</span>
                                <span class="block text-xs text-slate-400 mt-0.5">diterbitkan {{ $card->issued_at?->format('d M Y') }}</span>
                            @elseif ($card)
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">Dicabut</span>
                            @else
                                <span class="text-xs text-slate-400">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            @can('qr.manage')
                                <button wire:click="terbitkan({{ $s->id }})" class="text-xs font-semibold text-primary hover:underline">
                                    {{ $card && $card->isValid() ? 'Terbitkan Ulang' : 'Terbitkan' }}
                                </button>
                                @if ($card && $card->isValid())
                                    <button wire:click="cabut({{ $s->id }})" data-sims-confirm="Cabut kartu QR {{ $s->nama }}?"
                                            class="ml-2 text-xs font-semibold text-danger hover:underline">Cabut</button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Tidak ada siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $students->links() }}</div>

    @if ($showStudentId && isset($freshTokens[$showStudentId]))
        @php $siswaTampil = $students->firstWhere('id', $showStudentId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="tutupQr">
            <div class="bg-white dark:bg-boxdark rounded-2xl p-6 max-w-xs w-full text-center">
                <h3 class="font-bold text-slate-800 dark:text-white">{{ $siswaTampil?->nama }}</h3>
                <p class="text-xs text-slate-400 mb-3">NIS {{ $siswaTampil?->nis }} · Berlaku satu semester</p>
                <div class="bg-white p-3 rounded-xl inline-block">{!! \App\Domains\Pwa\Services\QrLoginService::qrSvg($freshTokens[$showStudentId]) !!}</div>
                <p class="text-[11px] text-slate-400 mt-3">Token hanya tampil sekali. Cetak / simpan sekarang.</p>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('qr.cetak', ['student_id' => $showStudentId]) }}" target="_blank"
                       class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white text-center">Cetak</a>
                    <button wire:click="tutupQr" class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
