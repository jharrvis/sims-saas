<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Domains\Pwa\Services\PushService;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Rombel;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Absensi harian per rombel (FR-601–606).
 */
class AttendanceIndex extends Component
{
    public ?int $rombelId = null;

    public string $tanggal;

    /** @var array<int, string> student_id => status */
    public array $marks = [];

    /** @var array<int, string> student_id => keterangan */
    public array $notes = [];

    public bool $locked = false;

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
        $taId = AcademicYear::where('aktif', true)->value('id');
        $this->rombelId = Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
            ->orderBy('nama')->value('id');

        $this->loadExisting();
    }

    public function updatedRombelId(): void
    {
        $this->loadExisting();
    }

    public function updatedTanggal(): void
    {
        $this->loadExisting();
    }

    private function loadExisting(): void
    {
        $this->marks = [];
        $this->notes = [];
        $this->locked = false;

        if (! $this->rombelId || $this->tanggal === '') {
            return;
        }

        $existing = Attendance::where('rombel_id', $this->rombelId)
            ->where('tanggal', $this->tanggal)
            ->get();

        foreach ($existing as $a) {
            $this->marks[$a->student_id] = $a->status;
            $this->notes[$a->student_id] = $a->keterangan ?? '';
            if ($a->isLocked()) {
                $this->locked = true;
            }
        }
    }

    public function markAll(string $status): void
    {
        if ($this->locked) {
            return;
        }

        foreach ($this->students() as $s) {
            $this->marks[$s->id] = $status;
        }
    }

    public function save(PushService $push): void
    {
        $this->authorize('absensi.create');

        if ($this->locked || ! $this->rombelId) {
            return;
        }

        $rombel = Rombel::findOrFail($this->rombelId);
        $alpa = collect();

        foreach ($this->students() as $s) {
            $status = $this->marks[$s->id] ?? null;
            if ($status === null || ! isset(Attendance::STATUS[$status])) {
                continue;
            }

            $record = Attendance::updateOrCreate(
                ['student_id' => $s->id, 'tanggal' => $this->tanggal],
                [
                    'rombel_id' => $rombel->id,
                    'status' => $status,
                    'keterangan' => $this->notes[$s->id] ?? null,
                    'input_by' => auth()->id(),
                ],
            );

            // Notifikasi orang tua hanya untuk alpa yang baru dicatat.
            if ($status === 'A' && $record->wasRecentlyCreated) {
                $alpa->push($s);
            }
        }

        $push->notifyAbsence($alpa, $this->tanggal);

        $this->loadExisting();
        session()->flash('saved', 'Absensi tersimpan.');
    }

    public function lock(): void
    {
        $this->authorize('absensi.kunci');

        Attendance::where('rombel_id', $this->rombelId)
            ->where('tanggal', $this->tanggal)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        $this->loadExisting();
    }

    /**
     * @return Collection<int, Student>
     */
    private function students()
    {
        return Rombel::find($this->rombelId)?->students()
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get() ?? collect();
    }

    public function render(): View
    {
        $taId = AcademicYear::where('aktif', true)->value('id');

        return view('livewire.akademik.attendance-index', [
            'rombels' => Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
                ->orderBy('nama')->get(),
            'students' => $this->rombelId ? $this->students() : collect(),
        ])->layout('layouts.app', ['title' => 'Absensi Siswa']);
    }
}
