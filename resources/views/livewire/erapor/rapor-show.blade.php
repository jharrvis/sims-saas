<div>
    @if (session('ok'))
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm print:hidden">{{ session('ok') }}</div>
    @endif

    <div class="flex flex-wrap items-center gap-3 mb-5 print:hidden">
        <a href="{{ route('erapor.index') }}" class="text-sm text-slate-500 hover:text-primary">← Kembali</a>
        @can('erapor.cetak')
            <button onclick="window.print()" class="ml-auto inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Rapor
            </button>
        @endcan
    </div>

    {{-- Kertas rapor --}}
    <div id="rapor" class="bg-white text-slate-800 rounded-xl border border-slate-200 p-6 sm:p-10 max-w-4xl mx-auto print:border-0 print:rounded-none print:max-w-none print:p-0">
        {{-- Kop sekolah --}}
        <div class="text-center border-b-4 border-double border-slate-800 pb-4 mb-6">
            <h1 class="text-xl font-extrabold uppercase">{{ $rapor['sekolah']->nama ?? 'Sekolah' }}</h1>
            @if (! empty($rapor['sekolah']->alamat))
                <p class="text-xs mt-1">{{ $rapor['sekolah']->alamat }}</p>
            @endif
            @if (! empty($rapor['sekolah']->telepon) || ! empty($rapor['sekolah']->email))
                <p class="text-xs">Telp: {{ $rapor['sekolah']->telepon ?? '-' }} · Email: {{ $rapor['sekolah']->email ?? '-' }}</p>
            @endif
        </div>

        <h2 class="text-center text-base font-bold uppercase tracking-wide mb-6">Rapor Hasil Belajar<br><span class="text-sm font-semibold normal-case">Semester {{ $rapor['semester']->nama }}</span></h2>

        {{-- Identitas siswa --}}
        <table class="text-sm mb-6">
            <tr><td class="pr-4 py-1 text-slate-500 w-40">Nama</td><td class="font-semibold">: {{ $rapor['siswa']->nama }}</td></tr>
            <tr><td class="pr-4 py-1 text-slate-500">NIS / NISN</td><td>: {{ $rapor['siswa']->nis }} / {{ $rapor['siswa']->nisn ?? '-' }}</td></tr>
        </table>

        {{-- Nilai --}}
        <table class="w-full text-sm border-collapse mb-6">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-300 px-3 py-2 text-left w-10">No</th>
                    <th class="border border-slate-300 px-3 py-2 text-left">Mata Pelajaran</th>
                    <th class="border border-slate-300 px-3 py-2 w-20">KKM</th>
                    <th class="border border-slate-300 px-3 py-2 w-20">Nilai</th>
                    <th class="border border-slate-300 px-3 py-2 w-20">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rapor['mapel'] as $i => $m)
                    <tr>
                        <td class="border border-slate-300 px-3 py-1.5 text-center">{{ $i + 1 }}</td>
                        <td class="border border-slate-300 px-3 py-1.5">{{ $m['nama'] }}</td>
                        <td class="border border-slate-300 px-3 py-1.5 text-center">{{ $m['kkm'] }}</td>
                        <td class="border border-slate-300 px-3 py-1.5 text-center font-semibold">{{ $m['nilai_akhir'] ?? '-' }}</td>
                        <td class="border border-slate-300 px-3 py-1.5 text-center">{{ $m['predikat'] }}</td>
                    </tr>
                @endforeach
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="3" class="border border-slate-300 px-3 py-1.5 text-right">Rata-rata</td>
                    <td class="border border-slate-300 px-3 py-1.5 text-center">{{ $rapor['rata_rata'] ?? '-' }}</td>
                    <td class="border border-slate-300 px-3 py-1.5 text-center">{{ $rapor['predikat_rata'] }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Absensi --}}
        <h3 class="font-bold text-sm mb-2">Ketidakhadiran</h3>
        <table class="text-sm border-collapse mb-6">
            <tr>
                <td class="border border-slate-300 px-4 py-1.5">Sakit</td><td class="border border-slate-300 px-4 py-1.5 text-center w-24">{{ $rapor['absensi']['S'] }} hari</td>
                <td class="border border-slate-300 px-4 py-1.5">Izin</td><td class="border border-slate-300 px-4 py-1.5 text-center w-24">{{ $rapor['absensi']['I'] }} hari</td>
                <td class="border border-slate-300 px-4 py-1.5">Alpa</td><td class="border border-slate-300 px-4 py-1.5 text-center w-24">{{ $rapor['absensi']['A'] }} hari</td>
            </tr>
        </table>

        {{-- Catatan wali --}}
        <h3 class="font-bold text-sm mb-2">Catatan Wali Kelas</h3>
        <div class="border border-slate-300 rounded p-3 text-sm min-h-[80px] mb-8">
            {{ $rapor['catatan_wali'] ?? '—' }}
        </div>

        {{-- Tanda tangan --}}
        <div class="flex justify-between text-sm mt-10">
            <div class="text-center">
                <p>Mengetahui,</p>
                <p>Kepala Sekolah</p>
                <div class="h-20"></div>
                <p class="font-semibold underline">………………………………</p>
            </div>
            <div class="text-center">
                <p>{{ now()->format('d F Y') }}</p>
                <p>Wali Kelas</p>
                <div class="h-20"></div>
                <p class="font-semibold underline">………………………………</p>
            </div>
        </div>
    </div>

    {{-- Form catatan wali --}}
    @can('erapor.catatan')
        <form wire:submit="simpanCatatan" class="mt-6 max-w-4xl mx-auto rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 print:hidden">
            <label for="catatan" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Catatan Wali Kelas</label>
            <textarea id="catatan" wire:model="catatan" rows="3" maxlength="2000" placeholder="Tulis catatan untuk rapor siswa ini…"
                      class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-sm outline-none focus:border-primary dark:text-white"></textarea>
            @error('catatan') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            <button type="submit" class="mt-3 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Simpan Catatan</button>
        </form>
    @endcan
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #rapor, #rapor * { visibility: visible; }
    #rapor { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>
