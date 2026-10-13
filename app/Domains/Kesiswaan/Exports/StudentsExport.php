<?php

declare(strict_types=1);

namespace App\Domains\Kesiswaan\Exports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Ekspor daftar siswa ke Excel (US: mengikuti filter aktif di daftar siswa).
 */
class StudentsExport implements FromCollection, WithHeadings
{
    /** @param Collection<int, Student> $students */
    public function __construct(private Collection $students) {}

    public function collection(): Collection
    {
        return $this->students->map(fn (Student $s) => [
            $s->nama,
            $s->nis,
            $s->nisn ?? '-',
            $s->jk === 'L' ? 'Laki-laki' : 'Perempuan',
            $s->rombels->first()?->nama ?? '-',
            ucfirst($s->status),
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'NIS', 'NISN', 'L/P', 'Kelas', 'Status'];
    }
}
