<?php

declare(strict_types=1);

namespace App\Livewire\Qr;

use App\Domains\Pwa\Services\QrLoginService;
use App\Models\Rombel;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Kelola kartu QR login siswa (Dok 09 §3.3).
 */
class QrCardIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $rombel_id = '';

    /** @var array<int,string> student_id => token plaintext (baru diterbitkan) */
    public array $freshTokens = [];

    public ?int $showStudentId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRombelId(): void
    {
        $this->resetPage();
    }

    public function terbitkan(int $studentId, QrLoginService $qr): void
    {
        $this->authorize('qr.manage');

        $student = Student::findOrFail($studentId);
        $token = $qr->issueCard($student, auth()->user());

        $fresh = session('qr_fresh_tokens', []);
        $fresh[$studentId] = $token;
        session(['qr_fresh_tokens' => $fresh]);

        $this->freshTokens = $fresh;
        $this->showStudentId = $studentId;

        session()->flash('ok', "Kartu QR untuk {$student->nama} diterbitkan.");
    }

    public function cabut(int $studentId, QrLoginService $qr): void
    {
        $this->authorize('qr.manage');

        $student = Student::findOrFail($studentId);

        if ($qr->revokeCard($student)) {
            session()->flash('ok', "Kartu QR {$student->nama} dicabut.");
        }

        $this->showStudentId = null;
    }

    public function terbitkanMassal(QrLoginService $qr): void
    {
        $this->authorize('qr.manage');
        $this->validate(['rombel_id' => ['required', 'integer', 'exists:rombels,id']]);

        $rombel = Rombel::findOrFail($this->rombel_id);
        $students = $rombel->students()->wherePivot('status', 'aktif')->get();

        $fresh = session('qr_fresh_tokens', []);
        foreach ($students as $student) {
            $fresh[$student->id] = $qr->issueCard($student, auth()->user());
        }
        session(['qr_fresh_tokens' => $fresh]);

        $this->redirect(route('qr.cetak', ['rombel_id' => $rombel->id]));
    }

    public function tutupQr(): void
    {
        $this->showStudentId = null;
    }

    public function render(): View
    {
        $this->authorize('qr.view');

        $students = Student::with(['qrCard' => fn ($q) => $q->orderByDesc('id')])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', '%'.$this->search.'%')
                ->orWhere('nis', 'like', '%'.$this->search.'%')))
            ->when($this->rombel_id !== '', fn ($q) => $q->whereHas('rombels', fn ($r) => $r
                ->where('rombels.id', $this->rombel_id)->where('class_student.status', 'aktif')))
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.qr.qr-card-index', [
            'students' => $students,
            'rombels' => Rombel::orderBy('tingkat')->orderBy('nama')->get(['id', 'nama', 'tingkat']),
        ])->layout('layouts.app', ['title' => 'Kartu QR Login']);
    }
}
