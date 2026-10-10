<?php

declare(strict_types=1);

namespace App\Livewire\Transportasi;

use App\Models\Student;
use App\Models\TransportRoute;
use App\Tenancy\TenantContext;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail rute: titik penjemputan + penumpang.
 */
class RouteShow extends Component
{
    public TransportRoute $route;

    public string $stop_nama = '';

    public string $stop_jam = '';

    public string $searchSiswa = '';

    public ?int $student_id = null;

    public ?int $stop_id = null;

    public function mount(TransportRoute $route): void
    {
        $this->authorize('transportasi.view');
        $this->route = $route;
    }

    public function tambahStop(): void
    {
        $this->authorize('transportasi.update');

        $this->validate([
            'stop_nama' => ['required', 'string', 'max:150'],
            'stop_jam' => ['nullable', 'date_format:H:i'],
        ]);

        $max = $this->route->stops()->max('urutan') ?? 0;

        $this->route->stops()->create([
            'nama' => $this->stop_nama,
            'urutan' => $max + 1,
            'jam_berangkat' => $this->stop_jam !== '' ? $this->stop_jam : null,
        ]);

        $this->reset(['stop_nama', 'stop_jam']);
        session()->flash('ok', 'Titik penjemputan ditambahkan.');
    }

    public function hapusStop(int $id): void
    {
        $this->authorize('transportasi.update');

        $this->route->stops()->whereKey($id)->delete();
        session()->flash('ok', 'Titik penjemputan dihapus.');
    }

    public function tambahPenumpang(): void
    {
        $this->authorize('transportasi.update');

        $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'stop_id' => ['nullable', 'integer', 'exists:transport_stops,id'],
        ]);

        abort_if($this->route->isPenuh(), 422, 'Kapasitas rute sudah penuh.');

        $this->route->members()->syncWithoutDetaching([
            $this->student_id => [
                'school_id' => app(TenantContext::class)->requireSchoolId(),
                'transport_stop_id' => $this->stop_id,
                'status' => 'aktif',
            ],
        ]);

        $this->reset(['student_id', 'stop_id', 'searchSiswa']);
        session()->flash('ok', 'Penumpang ditambahkan.');
    }

    public function keluarkanPenumpang(int $studentId): void
    {
        $this->authorize('transportasi.update');

        $this->route->members()->updateExistingPivot($studentId, ['status' => 'berhenti']);

        session()->flash('ok', 'Penumpang dikeluarkan.');
    }

    public function render(): View
    {
        $stops = $this->route->stops()->get();

        $penumpang = $this->route->members()
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get(['students.id', 'nis', 'nama']);

        $calon = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->whereNotIn('id', $penumpang->pluck('id'))
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nis', 'nama']);

        return view('livewire.transportasi.route-show', [
            'stops' => $stops,
            'penumpang' => $penumpang,
            'calon' => $calon,
        ])->layout('layouts.app', ['title' => $this->route->nama]);
    }
}
