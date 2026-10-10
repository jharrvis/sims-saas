<?php

declare(strict_types=1);

namespace App\Livewire\Ekskul;

use App\Models\Achievement;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar prestasi siswa.
 */
class AchievementIndex extends Component
{
    use WithPagination;

    public string $tingkat = '';

    public string $search = '';

    public ?int $student_id = null;

    public string $nama = '';

    public string $kategori = 'non_akademik';

    public string $tanggal = '';

    public string $keterangan = '';

    public string $searchSiswa = '';

    public bool $showForm = false;

    public function updatingTingkat(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->authorize('prestasi.create');

        $data = $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'nama' => ['required', 'string', 'max:200'],
            'kategori' => ['required', Rule::in(['akademik', 'non_akademik'])],
            'tingkat' => ['required', Rule::in(array_keys(Achievement::TINGKAT))],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        Achievement::create($data);

        $this->reset(['student_id', 'nama', 'keterangan', 'searchSiswa', 'showForm']);
        $this->kategori = 'non_akademik';
        $this->tanggal = '';
        session()->flash('ok', 'Prestasi dicatat.');
    }

    public function destroy(Achievement $achievement): void
    {
        $this->authorize('prestasi.delete');

        $achievement->delete();
        session()->flash('ok', 'Prestasi dihapus.');
    }

    public function render(): View
    {
        $items = Achievement::with('student:id,nis,nama')
            ->when($this->tingkat !== '', fn ($q) => $q->where('tingkat', $this->tingkat))
            ->when($this->search !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%'))
            ->orderByDesc('tanggal')
            ->paginate(15);

        $siswaList = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')->limit(10)->get(['id', 'nis', 'nama']);

        return view('livewire.ekskul.achievement-index', [
            'items' => $items,
            'siswaList' => $siswaList,
        ])->layout('layouts.app', ['title' => 'Prestasi Siswa']);
    }
}
