<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Rombel;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah rombel (FR-401, FR-303).
 */
class RombelForm extends Component
{
    public ?Rombel $rombel = null;

    public ?int $academic_year_id = null;

    public string $tingkat = '';

    public string $nama = '';

    public int $kapasitas = 36;

    public ?int $wali_kelas_id = null;

    public function mount(?Rombel $rombel, TenantContext $tenant): void
    {
        if ($rombel?->exists) {
            $this->rombel = $rombel;
            $this->academic_year_id = $rombel->academic_year_id;
            $this->tingkat = $rombel->tingkat;
            $this->nama = $rombel->nama;
            $this->kapasitas = $rombel->kapasitas;
            $this->wali_kelas_id = $rombel->wali_kelas_id;
        } else {
            $this->academic_year_id = AcademicYear::where('aktif', true)->value('id');
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        return [
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'tingkat' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', 'max:50', Rule::unique('rombels')->where(
                fn ($q) => $q->where('school_id', $schoolId)
                    ->where('academic_year_id', $this->academic_year_id)
            )->ignore($this->rombel?->id)],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:100'],
            'wali_kelas_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('school_id', $schoolId)],
        ];
    }

    public function save(): void
    {
        // Authorization berjalan sebelum validasi: route middleware hanya
        // melindungi GET, bukan aksi Livewire.
        $this->authorize($this->rombel?->exists ? 'kelas.update' : 'kelas.create');

        $this->validate();

        $data = [
            'academic_year_id' => $this->academic_year_id,
            'tingkat' => $this->tingkat,
            'nama' => $this->nama,
            'kapasitas' => $this->kapasitas,
            'wali_kelas_id' => $this->wali_kelas_id,
        ];

        if ($this->rombel?->exists) {
            $this->rombel->update($data);
        } else {
            Rombel::create($data);
        }

        $this->redirect(route('kelas.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.akademik.rombel-form', [
            'tahunAjaran' => AcademicYear::orderByDesc('tgl_mulai')->get(),
            'gurus' => Employee::whereIn('jenis', ['guru_mapel', 'guru_kelas'])
                ->where('status_aktif', true)->orderBy('nama')->get(),
        ])->layout('layouts.app', ['title' => $this->rombel?->exists ? 'Ubah Kelas' : 'Tambah Kelas']);
    }
}
