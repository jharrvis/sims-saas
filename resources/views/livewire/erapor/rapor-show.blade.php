<div class="page-stack">
    @if (session('ok'))
        <div class="management-alert management-alert-success print:hidden" role="status" aria-live="polite">
            <span>{{ session('ok') }}</span>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 print:hidden">
        <a href="{{ route('erapor.index') }}" class="management-back-link">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
        </a>
        @can('erapor.cetak')
            <button onclick="window.print()" class="btn btn-primary ml-auto">
                <x-icon name="printer" class="h-4 w-4" /> Cetak Rapor
            </button>
        @endcan
    </div>

    {{-- Kertas rapor: dipertahankan persis sebagai dokumen cetak (bukan chrome aplikasi). --}}
    <div id="rapor" class="bg-white text-slate-800 rounded-xl border border-slate-200 p-6 sm:p-10 max-w-4xl mx-auto min-w-0 overflow-x-auto print:overflow-visible print:border-0 print:rounded-none print:max-w-none print:p-0">
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
        <form wire:submit="simpanCatatan" class="card management-form-card max-w-4xl mx-auto print:hidden">
            <div class="form-group">
                <label for="catatan">Catatan Wali Kelas</label>
                <textarea id="catatan" wire:model="catatan" rows="3" maxlength="2000" placeholder="Tulis catatan untuk rapor siswa ini…"
                          class="textarea-field"></textarea>
                @error('catatan') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="simpanCatatan">
                    <span wire:loading.remove wire:target="simpanCatatan">Simpan Catatan</span>
                    <span wire:loading wire:target="simpanCatatan">Menyimpan...</span>
                </button>
            </div>
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
