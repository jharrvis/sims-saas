<?php

declare(strict_types=1);

namespace App\Domains\Kepegawaian\Services;

use App\Models\Employee;
use App\Models\Membership;

/**
 * Menyelaraskan status membership akun login tertaut dengan status data
 * kepegawaian (temuan audit QA B-01).
 *
 * Pegawai yang dinonaktifkan atau dipindah ke sampah tidak boleh
 * menyisakan akses sekolah yang aktif; pegawai yang aktif kembali /
 * dipulihkan mendapatkan kembali aksesnya. Hanya membership pada
 * sekolah pemilik data pegawai yang disentuh — membership user yang
 * sama di sekolah lain tidak terpengaruh.
 */
class EmployeeAccountSync
{
    public function syncMembershipStatus(Employee $employee): void
    {
        $user = $employee->user;

        if ($user === null || $employee->school_id === null) {
            return;
        }

        $status = $employee->status_aktif && ! $employee->trashed()
            ? 'aktif'
            : 'dinonaktifkan';

        Membership::query()
            ->where('user_id', $user->id)
            ->where('school_id', $employee->school_id)
            ->update(['status' => $status]);
    }
}
