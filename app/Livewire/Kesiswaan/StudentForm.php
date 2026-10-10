<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Domains\Platform\Services\QuotaService;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah siswa (FR-101).
 */
class StudentForm extends Component
{
    public ?Student $student = null;

    public string $nis = '';

    public string $nisn = '';

    public string $nama = '';

    public string $jk = 'L';

    public string $tempat_lahir = '';

    public string $tgl_lahir = '';

    public string $agama = '';

    public string $alamat = '';

    public string $phone = '';

    public function mount(?Student $student = null): void
    {
        if ($student?->exists) {
            $this->student = $student;
            $this->nis = $student->nis;
            $this->nisn = $student->nisn ?? '';
            $this->nama = $student->nama;
            $this->jk = $student->jk;
            $this->tempat_lahir = $student->tempat_lahir ?? '';
            $this->tgl_lahir = $student->tgl_lahir?->format('Y-m-d') ?? '';
            $this->agama = $student->agama ?? '';
            $this->alamat = $student->alamat ?? '';
            $this->phone = $student->phone ?? '';
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        return [
            'nis' => ['required', 'string', 'max:30', Rule::unique('students')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->student?->id)],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'jk' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Kuota siswa (trial: maks. 100) — hanya untuk siswa baru.
        if (! $this->student?->exists) {
            $quota = app(QuotaService::class)->check('siswa_aktif');

            if (! $quota['boleh']) {
                $this->addError('nama', 'Kuota siswa tercapai ('.$quota['terpakai'].'/'.$quota['batas'].'). Hubungi operator untuk upgrade paket.');

                return;
            }
        }

        $data = [
            'nis' => $this->nis,
            'nisn' => $this->nisn ?: null,
            'nama' => $this->nama,
            'jk' => $this->jk,
            'tempat_lahir' => $this->tempat_lahir ?: null,
            'tgl_lahir' => $this->tgl_lahir ?: null,
            'agama' => $this->agama ?: null,
            'alamat' => $this->alamat ?: null,
            'phone' => $this->phone ?: null,
        ];

        if ($this->student?->exists) {
            $this->student->update($data);
        } else {
            Student::create($data);
        }

        $this->redirect(route('siswa.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.kesiswaan.student-form')
            ->layout('layouts.app', ['title' => $this->student?->exists ? 'Ubah Siswa' : 'Tambah Siswa']);
    }
}
