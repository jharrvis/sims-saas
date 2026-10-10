<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Services;

use App\Models\Guardian;
use App\Models\Membership;
use App\Models\Student;
use App\Models\User;
use App\Scopes\SchoolScope;
use Spatie\Permission\PermissionRegistrar;

/**
 * Penautan identitas PWA: menghubungkan akun (login via WA OTP) ke data
 * siswa/wali berdasarkan nomor WA, plus membership + peran yang sesuai
 * (siswa / orang-tua) agar lolos ResolveTenant (Dok 02: wali/siswa → PWA).
 */
class PwaIdentityService
{
    /**
     * Tautkan akun ke satu siswa: users.student_id + membership + peran siswa.
     * Idempoten.
     */
    public function linkStudent(User $user, Student $student): void
    {
        if ($user->student_id !== $student->id) {
            $user->forceFill(['student_id' => $student->id])->save();
        }

        $this->ensureMembership($user, $student->school_id, 'siswa');
    }

    /**
     * Tautkan akun ke siswa/wali berdasarkan nomor WA. Idempoten.
     *
     * Pencarian identitas memakai kunci global yang eksplisit (nomor WA yang
     * sudah terverifikasi OTP): tenant scope dilepas karena pada titik ini
     * user justru belum punya membership/tenant. Aman karena nomor WA unik
     * per sekolah dan OTP sudah membuktikan kepemilikan nomor.
     */
    public function linkByPhone(User $user): void
    {
        $phone = self::normalize($user->phone ?? '');

        if ($phone === '') {
            return;
        }

        // 1. Akun siswa: cocokkan students.phone (1:1).
        if ($user->student_id === null) {
            $student = Student::withoutGlobalScope(SchoolScope::class)
                ->where('phone', $phone)
                ->first();

            if ($student !== null) {
                $this->linkStudent($user, $student);
            }
        }

        // 2. Akun orang tua: cocokkan guardians.no_wa (1:N lintas sekolah).
        $guardians = Guardian::withoutGlobalScope(SchoolScope::class)
            ->where('no_wa', $phone)
            ->get();

        foreach ($guardians as $guardian) {
            $user->guardians()->syncWithoutDetaching([$guardian->id]);
            $this->ensureMembership($user, $guardian->school_id, 'orang-tua');
        }
    }

    /**
     * Pastikan ada membership aktif + peran pada tenant terkait.
     */
    private function ensureMembership(User $user, int $schoolId, string $role): void
    {
        Membership::firstOrCreate(
            ['user_id' => $user->id, 'school_id' => $schoolId],
            ['status' => 'aktif']
        );

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($schoolId);

        try {
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        } finally {
            $registrar->setPermissionsTeamId(null);
        }
    }

    /**
     * Normalisasi nomor WA: hanya digit, 08xx → 62xx.
     */
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
