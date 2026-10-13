@extends('pdf.layout')

@section('title', 'Daftar Siswa')

@section('content')
<div class="judul-dok">Daftar Siswa</div>
<div class="subjudul">
    @if(!empty($tahunAjaran)) Tahun Ajaran: {{ $tahunAjaran->nama }} &nbsp;|&nbsp; @endif
    @if(!empty($filters['search'])) Pencarian: "{{ $filters['search'] }}" &nbsp;|&nbsp; @endif
    @if(!empty($filters['status'])) Status: {{ ucfirst($filters['status']) }} &nbsp;|&nbsp; @endif
    @if($rombel) Kelas: {{ $rombel->nama }} &nbsp;|&nbsp; @endif
    Total: {{ $students->count() }} siswa
</div>

<table class="data">
    <thead>
        <tr>
            <th class="num" style="width: 36px;">No</th>
            <th>Nama</th>
            <th style="width: 90px;">NIS</th>
            <th style="width: 110px;">NISN</th>
            <th style="width: 80px;">L/P</th>
            <th style="width: 90px;">Kelas</th>
            <th style="width: 80px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($students as $i => $siswa)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td>{{ $siswa->nama }}</td>
                <td>{{ $siswa->nis }}</td>
                <td>{{ $siswa->nisn ?? '-' }}</td>
                <td>{{ $siswa->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td>{{ $siswa->rombels->first()?->nama ?? '-' }}</td>
                <td>{{ ucfirst($siswa->status) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data siswa.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
