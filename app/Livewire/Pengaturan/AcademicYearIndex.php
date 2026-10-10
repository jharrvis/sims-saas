<?php

declare(strict_types=1);

namespace App\Livewire\Pengaturan;

use App\Models\AcademicYear;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Tahun ajaran: daftar, tambah, aktifkan.
 */
class AcademicYearIndex extends Component
{
    public string $nama = '';

    public string $tgl_mulai = '';

    public string $tgl_selesai = '';

    public function save(): void
    {
        $this->authorize('tahun-ajaran.create');

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:50'],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after:tgl_mulai'],
        ]);

        AcademicYear::create($data + ['aktif' => false]);

        $this->reset(['nama', 'tgl_mulai', 'tgl_selesai']);
        session()->flash('ok', 'Tahun ajaran ditambahkan.');
    }

    public function activate(AcademicYear $tahun): void
    {
        $this->authorize('tahun-ajaran.activate');

        AcademicYear::query()->update(['aktif' => false]);
        $tahun->update(['aktif' => true]);

        session()->flash('ok', "Tahun ajaran {$tahun->nama} diaktifkan.");
    }

    public function render(): View
    {
        return view('livewire.pengaturan.academic-year-index', [
            'items' => AcademicYear::orderByDesc('tgl_mulai')->get(),
        ])->layout('layouts.app', ['title' => 'Tahun Ajaran']);
    }
}
