<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Services;

use App\Models\Schedule;
use App\Tenancy\TenantContext;

/**
 * Deteksi bentrok jadwal (FR-502): guru dan ruang di hari+jam yang sama.
 */
class ScheduleConflictService
{
    public function __construct(private readonly TenantContext $tenant) {}

    /**
     * @param  array{rombel_id:int, academic_year_id:int, hari:string, jam_ke:int, employee_id:int, ruang:?string}  $data
     * @return array<string> daftar pesan bentrok (kosong = aman)
     */
    public function check(array $data, ?int $ignoreId = null): array
    {
        $schoolId = $this->tenant->requireSchoolId();
        $conflicts = [];

        $base = Schedule::where('school_id', $schoolId)
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('hari', $data['hari'])
            ->where('jam_ke', $data['jam_ke'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));

        // Bentrok guru: satu guru di dua kelas pada jam yang sama
        $guruClash = (clone $base)->where('employee_id', $data['employee_id'])->first();
        if ($guruClash) {
            $conflicts[] = "Guru sudah mengajar di kelas {$guruClash->rombel->nama} pada {$guruClash->labelHari()} jam ke-{$data['jam_ke']}.";
        }

        // Bentrok ruang: satu ruang dipakai dua kelas pada jam yang sama
        if (! empty($data['ruang'])) {
            $ruangClash = (clone $base)->where('ruang', $data['ruang'])->first();
            if ($ruangClash) {
                $conflicts[] = "Ruang {$data['ruang']} sudah dipakai kelas {$ruangClash->rombel->nama} pada {$ruangClash->labelHari()} jam ke-{$data['jam_ke']}.";
            }
        }

        return $conflicts;
    }
}
