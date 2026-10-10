<?php

declare(strict_types=1);

namespace App\Domains\Ppdb\Services;

use App\Models\PpdbApplicant;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Alur PPDB: verifikasi → seleksi → daftar ulang menjadi siswa.
 */
class PpdbService
{
    public function __construct(private readonly TenantContext $tenant) {}

    /**
     * Generate nomor pendaftaran unik per sekolah: PPDB/{tahun}/{urut}.
     */
    public function generateNoPendaftaran(): string
    {
        $schoolId = $this->tenant->requireSchoolId();
        $tahun = now()->format('Y');

        $count = PpdbApplicant::where('school_id', $schoolId)
            ->whereYear('created_at', $tahun)
            ->count() + 1;

        return sprintf('PPDB/%s/%04d', $tahun, $count);
    }

    /**
     * Ubah status pendaftar mengikuti alur yang sah.
     *
     * @throws \InvalidArgumentException jika transisi tidak sah
     */
    public function setStatus(PpdbApplicant $applicant, string $status, ?string $catatan = null): PpdbApplicant
    {
        $allowed = [
            'draft' => ['submitted'],
            'submitted' => ['verified', 'rejected'],
            'verified' => ['accepted', 'rejected'],
            'accepted' => ['enrolled', 'rejected'],
            'rejected' => [],
            'enrolled' => [],
        ];

        if (! in_array($status, $allowed[$applicant->status], true)) {
            throw new \InvalidArgumentException(
                "Transisi status {$applicant->status} → {$status} tidak diizinkan."
            );
        }

        $applicant->update([
            'status' => $status,
            'catatan' => $catatan ?? $applicant->catatan,
        ]);

        return $applicant->fresh();
    }

    /**
     * Daftar ulang: pendaftar yang diterima menjadi data siswa.
     *
     * @throws \InvalidArgumentException jika status bukan accepted
     */
    public function enroll(PpdbApplicant $applicant): Student
    {
        if ($applicant->status !== 'accepted') {
            throw new \InvalidArgumentException('Hanya pendaftar berstatus diterima yang bisa daftar ulang.');
        }

        return DB::transaction(function () use ($applicant): Student {
            $student = Student::create([
                'nis' => $this->generateNis(),
                'nisn' => $applicant->nisn,
                'nama' => $applicant->nama,
                'jk' => $applicant->jk,
                'tgl_lahir' => $applicant->tgl_lahir,
                'asal_sekolah' => $applicant->asal_sekolah,
                'status' => 'aktif',
            ]);

            $applicant->update([
                'status' => 'enrolled',
                'student_id' => $student->id,
            ]);

            return $student;
        });
    }

    private function generateNis(): string
    {
        $schoolId = $this->tenant->requireSchoolId();
        $tahun = now()->format('y');

        $count = Student::where('school_id', $schoolId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('%s%04d', $tahun, $count);
    }
}
