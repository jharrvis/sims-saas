<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu QR Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; padding: 24px; color: #1e293b; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .sub { font-size: 12px; color: #64748b; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .kartu { border: 2px solid #1e293b; border-radius: 12px; padding: 14px; text-align: center; page-break-inside: avoid; }
        .kartu .nama { font-weight: 700; font-size: 14px; margin-bottom: 2px; }
        .kartu .nis { font-size: 11px; color: #64748b; margin-bottom: 8px; }
        .kartu svg { width: 150px; height: 150px; }
        .kartu .note { font-size: 10px; color: #64748b; margin-top: 8px; }
        .kosong { color: #64748b; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px">
        <button onclick="window.print()" style="background:#2563eb;color:#fff;border:0;border-radius:8px;padding:10px 20px;font-weight:600;cursor:pointer">Cetak</button>
        <a href="{{ route('qr.kartu') }}" style="margin-left:12px;color:#64748b">← Kembali</a>
    </div>

    <h1>Kartu QR Login Siswa</h1>
    <p class="sub">Pindai di halaman login PWA pada perangkat bersama. Berlaku satu semester — jangan dibagikan ke orang lain.</p>

    @if ($kartu->isEmpty())
        <p class="kosong">Tidak ada kartu untuk dicetak. Terbitkan kartu terlebih dahulu dari halaman Kartu QR.</p>
    @else
        <div class="grid">
            @foreach ($kartu as $k)
                <div class="kartu">
                    <div class="nama">{{ $k['student']->nama }}</div>
                    <div class="nis">NIS {{ $k['student']->nis }}</div>
                    {!! \App\Domains\Pwa\Services\QrLoginService::qrSvg($k['token'], 150) !!}
                    <div class="note">Berlaku semester ini</div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
