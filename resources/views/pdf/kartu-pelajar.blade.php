<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kartu Pelajar</title>
<style>
    @page { margin: 10mm 10mm 10mm 10mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; color: #1e293b; }
    table.grid { width: 100%; border-collapse: separate; border-spacing: 6mm 5mm; }
    table.grid td.slot { width: 50%; vertical-align: top; }
    .kartu {
        width: 85mm; height: 55mm;
        border: 1.5pt solid #1e40af; border-radius: 4mm;
        overflow: hidden; background: #ffffff;
    }
    .kartu-head {
        background: #1e40af; color: #ffffff;
        padding: 2mm 3mm; height: 11mm;
    }
    .kartu-head .sekolah { font-size: 10pt; font-weight: bold; line-height: 1.2; }
    .kartu-head .label { font-size: 7pt; letter-spacing: 2pt; opacity: 0.85; }
    .kartu-body { padding: 2.5mm 3mm; }
    .kartu-body table { width: 100%; border-collapse: collapse; }
    .kartu-body td { vertical-align: top; }
    td.foto { width: 24mm; }
    .foto-img { width: 22mm; height: 28mm; object-fit: cover; border-radius: 1mm; border: 0.5pt solid #cbd5e1; }
    .foto-awal {
        width: 22mm; height: 28mm; border-radius: 1mm; background: #dbeafe;
        color: #1e40af; font-size: 16pt; font-weight: bold;
        text-align: center; line-height: 28mm;
    }
    td.data { padding-left: 3mm; }
    .nama { font-size: 11pt; font-weight: bold; line-height: 1.25; margin-bottom: 1mm; }
    .field { font-size: 8pt; line-height: 1.5; color: #475569; }
    .field b { color: #1e293b; }
    td.qr { width: 20mm; text-align: right; }
    .qr-img { width: 19mm; height: 19mm; }
    .page-break { page-break-after: always; }
</style>
</head>
<body>
@foreach (array_chunk($kartu, 8) as $pageIndex => $halaman)
    <table class="grid {{ $loop->last ? '' : 'page-break' }}">
        @foreach (array_chunk($halaman, 2) as $baris)
            <tr>
                @foreach ($baris as $k)
                    <td class="slot">
                        <div class="kartu">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td class="kartu-head">
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                @if(!empty($logoAbs))
                                                    <td style="width: 10mm;"><img src="{{ $logoAbs }}" style="height: 8mm;"></td>
                                                @endif
                                                <td>
                                                    <div class="sekolah">{{ $sekolah->nama ?? 'Sekolah' }}</div>
                                                    <div class="label">KARTU PELAJAR</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="kartu-body">
                                        <table>
                                            <tr>
                                                <td class="foto">
                                                    @if($k['fotoAbs'])
                                                        <img class="foto-img" src="{{ $k['fotoAbs'] }}">
                                                    @else
                                                        <div class="foto-awal">{{ strtoupper(mb_substr($k['siswa']->nama, 0, 1)) }}</div>
                                                    @endif
                                                </td>
                                                <td class="data">
                                                    <div class="nama">{{ $k['siswa']->nama }}</div>
                                                    <div class="field">NIS: <b>{{ $k['siswa']->nis }}</b></div>
                                                    <div class="field">NISN: <b>{{ $k['siswa']->nisn ?? '-' }}</b></div>
                                                    <div class="field">Kelas: <b>{{ $k['rombel']?->nama ?? '-' }}</b></div>
                                                    <div class="field">Tahun: <b>{{ $k['rombel']?->academicYear?->nama ?? '-' }}</b></div>
                                                </td>
                                                <td class="qr">
                                                    <img class="qr-img" src="{{ $k['qr'] }}">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                @endforeach
                @if(count($baris) < 2)
                    <td class="slot"></td>
                @endif
            </tr>
        @endforeach
    </table>
@endforeach
</body>
</html>
