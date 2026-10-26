<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\Subject;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah mata pelajaran (FR-402).
 *
 * Rute /mapel/tambah dan /mapel/{subject}/ubah di web.php sudah disiapkan
 * dengan guard class_exists — rute itu baru terdaftar setelah class ini ada.
 */
class SubjectForm extends Component
{
    public ?Subject $subject = null;

    public string $kode = '';

    public string $nama = '';

    public string $kelompok = 'wajib';

    public float $kkm_default = 75.0;

    public function mount(?Subject $subject): void
    {
        if ($subject?->exists) {
            $this->subject = $subject;
            $this->kode = $subject->kode;
            $this->nama = $subject->nama;
            $this->kelompok = $subject->kelompok;
            $this->kkm_default = (float) $subject->kkm_default;
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('subjects')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->subject?->id)],
            'nama' => ['required', 'string', 'max:100'],
            'kelompok' => ['required', 'string', Rule::in(['wajib', 'peminatan', 'mulok'])],
            'kkm_default' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function save(): void
    {
        // Authorization berjalan sebelum validasi: route middleware hanya
        // melindungi GET, bukan aksi Livewire.
        $this->authorize($this->subject?->exists ? 'jadwal.update' : 'jadwal.create');

        $this->validate();

        $data = [
            'kode' => $this->kode,
            'nama' => $this->nama,
            'kelompok' => $this->kelompok,
            'kkm_default' => $this->kkm_default,
        ];

        if ($this->subject?->exists) {
            $this->subject->update($data);
        } else {
            Subject::create($data);
        }

        $this->redirect(route('mapel.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.akademik.subject-form', [
            'kelompokOptions' => [
                'wajib' => 'Wajib',
                'peminatan' => 'Peminatan',
                'mulok' => 'Muatan Lokal (Mulok)',
            ],
        ])->layout('layouts.app', ['title' => $this->subject?->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran']);
    }
}
