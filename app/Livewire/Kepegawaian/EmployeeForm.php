<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Models\Employee;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah guru & pegawai (FR-301).
 */
class EmployeeForm extends Component
{
    public ?Employee $employee = null;

    public string $nip_nuptk = '';

    public string $nama = '';

    public string $jenis = 'guru_mapel';

    public string $status_kepegawaian = 'honorer';

    public string $jabatan = '';

    public bool $status_aktif = true;

    public function mount(?Employee $employee = null): void
    {
        if ($employee?->exists) {
            $this->employee = $employee;
            $this->nip_nuptk = $employee->nip_nuptk ?? '';
            $this->nama = $employee->nama;
            $this->jenis = $employee->jenis;
            $this->status_kepegawaian = $employee->status_kepegawaian;
            $this->jabatan = $employee->jabatan ?? '';
            $this->status_aktif = $employee->status_aktif;
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        return [
            'nip_nuptk' => ['nullable', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(['guru_mapel', 'guru_kelas', 'tendik'])],
            'status_kepegawaian' => ['required', Rule::in(['pns', 'pppk', 'honorer', 'kontrak'])],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'status_aktif' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nip_nuptk' => $this->nip_nuptk ?: null,
            'nama' => $this->nama,
            'jenis' => $this->jenis,
            'status_kepegawaian' => $this->status_kepegawaian,
            'jabatan' => $this->jabatan ?: null,
            'status_aktif' => $this->status_aktif,
        ];

        if ($this->employee?->exists) {
            $this->employee->update($data);
        } else {
            Employee::create($data);
        }

        $this->redirect(route('guru.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.kepegawaian.employee-form')
            ->layout('layouts.app', ['title' => $this->employee?->exists ? 'Ubah Guru' : 'Tambah Guru']);
    }
}
