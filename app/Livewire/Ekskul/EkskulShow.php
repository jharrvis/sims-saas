<?php

declare(strict_types=1);

namespace App\Livewire\Ekskul;

use App\Models\Extracurricular;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail ekskul + kelola anggota.
 */
class EkskulShow extends Component
{
    public Extracurricular $ekskul;

    public string $searchSiswa = '';

    public function mount(Extracurricular $ekskul): void
    {
        $this->authorize('ekskul.view');
        $this->ekskul = $ekskul;
    }

    public function tambahAnggota(int $studentId): void
    {
        $this->authorize('ekskul.update');

        $this->ekskul->members()->syncWithoutDetaching([
            $studentId => [
                'school_id' => app(TenantContext::class)->requireSchoolId(),
                'tanggal_gabung' => now()->toDateString(),
                'status' => 'aktif',
            ],
        ]);

        session()->flash('ok', 'Anggota ditambahkan.');
    }

    public function keluarkanAnggota(int $studentId): void
    {
        $this->authorize('ekskul.update');

        $this->ekskul->members()->updateExistingPivot($studentId, ['status' => 'keluar']);

        session()->flash('ok', 'Anggota dikeluarkan.');
    }

    public function render(): View
    {
        $anggota = $this->ekskul->members()
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get(['students.id', 'nis', 'nama']);

        $calon = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->whereNotIn('id', $anggota->pluck('id'))
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nis', 'nama']);

        return view('livewire.ekskul.ekskul-show', [
            'anggota' => $anggota,
            'calon' => $calon,
        ])->layout('layouts.app', ['title' => $this->ekskul->nama]);
    }
}
