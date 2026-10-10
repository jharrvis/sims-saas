<?php

declare(strict_types=1);

namespace App\Livewire\Alumni;

use App\Models\Alumni;
use App\Models\AlumniCareer;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail alumni + riwayat karier (tracer study).
 */
class AlumniShow extends Component
{
    public Alumni $alumni;

    public string $status = 'kerja';

    public string $institusi = '';

    public string $jurusan_posisi = '';

    public string $tahun_mulai = '';

    public string $keterangan = '';

    public bool $showForm = false;

    public function mount(Alumni $alumni): void
    {
        $this->authorize('alumni.view');
        $this->alumni = $alumni;
    }

    public function tambahKarier(): void
    {
        $this->authorize('alumni.update');

        $data = $this->validate([
            'status' => ['required', Rule::in(array_keys(AlumniCareer::STATUS))],
            'institusi' => ['nullable', 'string', 'max:200'],
            'jurusan_posisi' => ['nullable', 'string', 'max:200'],
            'tahun_mulai' => ['nullable', 'string', 'max:9'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->alumni->careers()->create($data);

        $this->reset(['institusi', 'jurusan_posisi', 'tahun_mulai', 'keterangan', 'showForm']);
        $this->status = 'kerja';
        session()->flash('ok', 'Riwayat karier ditambahkan.');
    }

    public function hapusKarier(int $id): void
    {
        $this->authorize('alumni.update');

        $this->alumni->careers()->whereKey($id)->delete();
        session()->flash('ok', 'Riwayat karier dihapus.');
    }

    public function render(): View
    {
        $careers = $this->alumni->careers()
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('id')
            ->get();

        return view('livewire.alumni.alumni-show', ['careers' => $careers])
            ->layout('layouts.app', ['title' => $this->alumni->nama]);
    }
}
