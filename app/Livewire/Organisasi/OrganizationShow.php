<?php

declare(strict_types=1);

namespace App\Livewire\Organisasi;

use App\Models\Organization;
use App\Models\OrganizationActivity;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail organisasi: pengurus + kegiatan.
 */
class OrganizationShow extends Component
{
    public Organization $organization;

    public string $searchSiswa = '';

    public ?int $student_id = null;

    public string $jabatan = '';

    public string $periode = '';

    public string $kegiatan_nama = '';

    public string $kegiatan_mulai = '';

    public string $kegiatan_selesai = '';

    public string $kegiatan_status = 'rencana';

    public bool $showKegiatanForm = false;

    public function mount(Organization $organization): void
    {
        $this->authorize('organisasi.view');
        $this->organization = $organization;
    }

    public function tambahPengurus(): void
    {
        $this->authorize('organisasi.update');

        $this->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'periode' => ['nullable', 'string', 'max:20'],
        ]);

        $this->organization->members()->syncWithoutDetaching([
            $this->student_id => [
                'school_id' => app(TenantContext::class)->requireSchoolId(),
                'jabatan' => $this->jabatan !== '' ? $this->jabatan : null,
                'periode' => $this->periode !== '' ? $this->periode : null,
                'status' => 'aktif',
            ],
        ]);

        $this->reset(['student_id', 'jabatan', 'periode', 'searchSiswa']);
        session()->flash('ok', 'Pengurus ditambahkan.');
    }

    public function keluarkanPengurus(int $studentId): void
    {
        $this->authorize('organisasi.update');

        $this->organization->members()->updateExistingPivot($studentId, ['status' => 'nonaktif']);

        session()->flash('ok', 'Pengurus dinonaktifkan.');
    }

    public function tambahKegiatan(): void
    {
        $this->authorize('organisasi.update');

        $data = $this->validate([
            'kegiatan_nama' => ['required', 'string', 'max:200'],
            'kegiatan_mulai' => ['nullable', 'date'],
            'kegiatan_selesai' => ['nullable', 'date', 'after_or_equal:kegiatan_mulai'],
            'kegiatan_status' => ['required', Rule::in(array_keys(OrganizationActivity::STATUS))],
        ]);

        $this->organization->activities()->create([
            'nama' => $data['kegiatan_nama'],
            'tgl_mulai' => $data['kegiatan_mulai'] ?: null,
            'tgl_selesai' => $data['kegiatan_selesai'] ?: null,
            'status' => $data['kegiatan_status'],
        ]);

        $this->reset(['kegiatan_nama', 'kegiatan_mulai', 'kegiatan_selesai', 'showKegiatanForm']);
        $this->kegiatan_status = 'rencana';
        session()->flash('ok', 'Kegiatan ditambahkan.');
    }

    public function hapusKegiatan(int $id): void
    {
        $this->authorize('organisasi.update');

        $this->organization->activities()->whereKey($id)->delete();
        session()->flash('ok', 'Kegiatan dihapus.');
    }

    public function render(): View
    {
        $pengurus = $this->organization->members()
            ->wherePivot('status', 'aktif')
            ->orderBy('nama')
            ->get(['students.id', 'nis', 'nama']);

        $calon = Student::where('status', 'aktif')
            ->when($this->searchSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$this->searchSiswa.'%'))
            ->whereNotIn('id', $pengurus->pluck('id'))
            ->orderBy('nama')
            ->limit(10)
            ->get(['id', 'nis', 'nama']);

        $kegiatan = $this->organization->activities()
            ->orderByDesc('tgl_mulai')
            ->get();

        return view('livewire.organisasi.organization-show', [
            'pengurus' => $pengurus,
            'calon' => $calon,
            'kegiatan' => $kegiatan,
        ])->layout('layouts.app', ['title' => $this->organization->nama]);
    }
}
