<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Models\Employee;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar guru & pegawai dengan pencarian + filter jenis (FR-301).
 */
class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $jenis = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingJenis(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $employees = Employee::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nip_nuptk', 'like', '%'.$this->search.'%');
            }))
            ->when($this->jenis !== '', fn ($q) => $q->where('jenis', $this->jenis))
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.kepegawaian.employee-index', [
            'employees' => $employees,
        ])->layout('layouts.app', ['title' => 'Data Guru']);
    }
}
