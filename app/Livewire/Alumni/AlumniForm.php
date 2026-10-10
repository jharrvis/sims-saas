<?php

declare(strict_types=1);

namespace App\Livewire\Alumni;

use App\Models\Alumni;
use App\Models\Student;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form tambah/ubah data alumni.
 */
class AlumniForm extends Component
{
    public ?Alumni $alumni = null;

    public ?int $student_id = null;

    public string $searchSiswa = '';

    public string $nama = '';

    public string $tahun_lulus = '';

    public string $phone = '';

    public string $email = '';

    public string $alamat = '';

    public function mount(?Alumni $alumni = null): void
    {
        $this->authorize($alumni?->exists ? 'alumni.update' : 'alumni.create');

        if ($alumni?->exists) {
            $this->alumni = $alumni;
            $this->student_id = $alumni->student_id;
            $this->nama = $alumni->nama;
            $this->tahun_lulus = $alumni->tahun_lulus ?? '';
            $this->phone = $alumni->phone ?? '';
            $this->email = $alumni->email ?? '';
            $this->alamat = $alumni->alamat ?? '';
        }
    }

    public function updatedStudentId(): void
    {
        if ($this->student_id !== null && $this->nama === '') {
            $this->nama = (string) Student::whereKey($this->student_id)->value('nama');
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'nama' => ['required', 'string', 'max:150'],
            'tahun_lulus' => ['nullable', 'string', 'max:9'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = [
            'student_id' => $data['student_id'],
            'nama' => $data['nama'],
            'tahun_lulus' => $data['tahun_lulus'] ?: null,
            'phone' => $data['phone'] ?: null,
            'email' => $data['email'] ?: null,
            'alamat' => $data['alamat'] ?: null,
        ];

        if ($this->alumni?->exists) {
            $this->alumni->update($payload);
            session()->flash('ok', 'Data alumni diperbarui.');
        } else {
            Alumni::create($payload);
            session()->flash('ok', 'Data alumni ditambahkan.');
        }

        $this->redirectRoute('alumni.index');
    }

    public function render(): View
    {
        $siswaList = Student::when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->orderBy('nama')->limit(10)->get(['id', 'nis', 'nama']);

        return view('livewire.alumni.alumni-form', [
            'siswaList' => $siswaList,
            'isEdit' => (bool) $this->alumni?->exists,
        ])->layout('layouts.app', ['title' => $this->alumni?->exists ? 'Ubah Alumni' : 'Tambah Alumni']);
    }
}
