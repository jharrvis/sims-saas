<?php

declare(strict_types=1);

namespace App\Livewire\Tatib;

use App\Models\Student;
use App\Models\Violation;
use App\Models\ViolationType;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form catat pelanggaran siswa.
 */
class ViolationForm extends Component
{
    public ?Violation $violation = null;

    public ?int $student_id = null;

    public ?int $violation_type_id = null;

    public string $tanggal = '';

    public string $keterangan = '';

    public string $tindak_lanjut = '';

    public string $searchSiswa = '';

    public function mount(?Violation $violation = null): void
    {
        $this->authorize($violation?->exists ? 'tatib.update' : 'tatib.create');
        $this->tanggal = now()->toDateString();

        if ($violation?->exists) {
            $this->violation = $violation;
            $this->student_id = $violation->student_id;
            $this->violation_type_id = $violation->violation_type_id;
            /** @var Carbon $tgl */
            $tgl = $violation->tanggal;
            $this->tanggal = $tgl->toDateString();
            $this->keterangan = $violation->keterangan ?? '';
            $this->tindak_lanjut = $violation->tindak_lanjut ?? '';
            $this->searchSiswa = $violation->student->nama;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'violation_type_id' => ['required', 'integer', 'exists:violation_types,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:200'],
        ]);

        $payload = $data + ['dilaporkan_oleh' => auth()->id()];

        if ($this->violation?->exists) {
            $this->violation->update($payload);
            session()->flash('ok', 'Catatan pelanggaran diperbarui.');
        } else {
            Violation::create($payload);
            session()->flash('ok', 'Pelanggaran dicatat.');
        }

        $this->redirectRoute('tatib.index');
    }

    public function render(): View
    {
        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nis', 'nama']);

        return view('livewire.tatib.violation-form', [
            'siswaList' => $siswaList,
            'types' => ViolationType::where('aktif', true)->orderBy('nama')->get(),
            'isEdit' => (bool) $this->violation?->exists,
        ])->layout('layouts.app', ['title' => $this->violation?->exists ? 'Ubah Pelanggaran' : 'Catat Pelanggaran']);
    }
}
