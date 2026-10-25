<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Domains\Akademik\Services\ScheduleConflictService;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\Subject;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah jadwal dengan deteksi bentrok (FR-502).
 */
class ScheduleForm extends Component
{
    public ?int $rombel_id = null;

    public ?int $academic_year_id = null;

    public string $hari = 'senin';

    public int $jam_ke = 1;

    public ?int $subject_id = null;

    public ?int $employee_id = null;

    public string $ruang = '';

    /** @var array<string> */
    public array $conflicts = [];

    public function mount(): void
    {
        $this->academic_year_id = AcademicYear::where('aktif', true)->value('id');
        $this->rombel_id = Rombel::when($this->academic_year_id, fn ($q) => $q->where('academic_year_id', $this->academic_year_id))
            ->orderBy('nama')->value('id');
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        return [
            'rombel_id' => ['required', 'integer', Rule::exists('rombels', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'hari' => ['required', Rule::in(Schedule::HARIS)],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:15'],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('school_id', $schoolId)],
            'ruang' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function save(ScheduleConflictService $conflict): void
    {
        $this->authorize('jadwal.create');

        $data = $this->validate();

        $this->conflicts = $conflict->check([
            'rombel_id' => $data['rombel_id'],
            'academic_year_id' => $data['academic_year_id'],
            'hari' => $data['hari'],
            'jam_ke' => $data['jam_ke'],
            'employee_id' => $data['employee_id'],
            'ruang' => $data['ruang'] ?: null,
        ]);

        if ($this->conflicts !== []) {
            return;
        }

        Schedule::create($data);

        $this->redirect(route('jadwal.index', ['rombel' => $data['rombel_id']]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.akademik.schedule-form', [
            'rombels' => Rombel::when($this->academic_year_id, fn ($q) => $q->where('academic_year_id', $this->academic_year_id))
                ->orderBy('nama')->get(),
            'subjects' => Subject::orderBy('nama')->get(),
            'gurus' => Employee::whereIn('jenis', ['guru_mapel', 'guru_kelas'])
                ->where('status_aktif', true)->orderBy('nama')->get(),
        ])->layout('layouts.app', ['title' => 'Tambah Jadwal']);
    }
}
