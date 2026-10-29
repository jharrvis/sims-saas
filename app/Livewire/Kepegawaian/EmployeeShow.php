<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Models\Employee;
use App\Models\Schedule;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail guru/pegawai: biodata + rombel sebagai wali kelas + jadwal mengajar.
 */
class EmployeeShow extends Component
{
    public Employee $employee;

    public function mount(Employee $employee): void
    {
        $this->employee = $employee->load('rombelsAsWali', 'user.roles', 'user.memberships');
    }

    public function render(): View
    {
        // Jadwal mengajar dikelompokkan per hari, urut Senin–Sabtu.
        // Batas 200 baris sebagai pengaman; jadwal mingguan satu guru
        // secara realistis jauh di bawah itu (QA B-18).
        $jadwal = Schedule::with(['subject:id,nama,kode', 'rombel:id,nama'])
            ->where('employee_id', $this->employee->id)
            ->orderBy('jam_ke')
            ->limit(200)
            ->get();

        $urutanHari = array_flip(Schedule::HARIS);

        /** @var Collection<string, Collection<int, Schedule>> $jadwalPerHari */
        $jadwalPerHari = $jadwal->groupBy('hari')
            ->sortKeysUsing(fn (string $a, string $b): int => ($urutanHari[$a] ?? 99) <=> ($urutanHari[$b] ?? 99));

        // Akun login yang tertaut (bila ada) + statusnya di sekolah aktif.
        $akun = null;
        if ($this->employee->user !== null) {
            $schoolId = (int) app(TenantContext::class)->schoolId();
            $akun = [
                'id' => $this->employee->user->id,
                'username' => $this->employee->user->username,
                // Hanya peran pada tim sekolah pegawai ini — getRoleNames()
                // tanpa konteks tim mencampur peran dari sekolah lain
                // untuk pengguna multi-sekolah (QA B-18).
                'roles' => $this->employee->user->roles
                    ->where('team_id', $this->employee->school_id)
                    ->pluck('name')
                    ->all(),
                'status' => $this->employee->user->memberships
                    ->where('school_id', $schoolId)->first()?->status,
            ];
        }

        return view('livewire.kepegawaian.employee-show', [
            'jadwalPerHari' => $jadwalPerHari,
            'akun' => $akun,
        ])->layout('layouts.app', ['title' => 'Detail Guru']);
    }
}
