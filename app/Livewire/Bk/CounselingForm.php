<?php

declare(strict_types=1);

namespace App\Livewire\Bk;

use App\Models\CounselingSession;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form jadwal + hasil sesi konseling.
 */
class CounselingForm extends Component
{
    public ?CounselingSession $session = null;

    public ?int $student_id = null;

    public string $tanggal = '';

    public string $jenis = 'individu';

    public string $topik = '';

    public string $catatan = '';

    public string $tindak_lanjut = '';

    public string $status = 'terjadwal';

    public string $searchSiswa = '';

    public function mount(?CounselingSession $session = null): void
    {
        $this->authorize($session?->exists ? 'bk.update' : 'bk.create');
        $this->tanggal = now()->toDateString();

        if ($session?->exists) {
            $this->session = $session;
            $this->student_id = $session->student_id;
            /** @var Carbon $tgl */
            $tgl = $session->tanggal;
            $this->tanggal = $tgl->toDateString();
            $this->jenis = $session->jenis;
            $this->topik = $session->topik;
            $this->catatan = $session->catatan ?? '';
            $this->tindak_lanjut = $session->tindak_lanjut ?? '';
            $this->status = $session->status;
            $this->searchSiswa = $session->student->nama;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'tanggal' => ['required', 'date'],
            'jenis' => ['required', Rule::in(['individu', 'kelompok', 'klasikal'])],
            'topik' => ['required', 'string', 'max:200'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:200'],
            'status' => ['required', Rule::in(['terjadwal', 'selesai', 'batal'])],
        ]);

        $payload = $data + ['counselor_id' => auth()->id()];

        if ($this->session?->exists) {
            $this->session->update($payload);
            session()->flash('ok', 'Sesi konseling diperbarui.');
        } else {
            CounselingSession::create($payload);
            session()->flash('ok', 'Sesi konseling dijadwalkan.');
        }

        $this->redirectRoute('bk.index');
    }

    public function render(): View
    {
        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nis', 'nama']);

        return view('livewire.bk.counseling-form', [
            'siswaList' => $siswaList,
            'isEdit' => (bool) $this->session?->exists,
        ])->layout('layouts.app', ['title' => $this->session?->exists ? 'Ubah Sesi' : 'Jadwalkan Konseling']);
    }
}
