<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Models\Employee;
use App\Models\Schedule;
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
        $this->employee = $employee->load('rombelsAsWali');
    }

    public function render(): View
    {
        // Jadwal mengajar dikelompokkan per hari, urut Senin–Sabtu.
        $jadwal = Schedule::with(['subject:id,nama,kode', 'rombel:id,nama'])
            ->where('employee_id', $this->employee->id)
            ->orderBy('jam_ke')
            ->get();

        $urutanHari = array_flip(Schedule::HARIS);

        /** @var Collection<string, Collection<int, Schedule>> $jadwalPerHari */
        $jadwalPerHari = $jadwal->groupBy('hari')
            ->sortKeysUsing(fn (string $a, string $b): int => ($urutanHari[$a] ?? 99) <=> ($urutanHari[$b] ?? 99));

        return view('livewire.kepegawaian.employee-show', [
            'jadwalPerHari' => $jadwalPerHari,
        ])->layout('layouts.app', ['title' => 'Detail Guru']);
    }
}
