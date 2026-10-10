<?php

declare(strict_types=1);

namespace App\Livewire\Pengumuman;

use App\Models\Announcement;
use App\Models\Rombel;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Permission\Models\Role;

/**
 * Form tambah/ubah pengumuman.
 */
class AnnouncementForm extends Component
{
    public ?Announcement $announcement = null;

    public string $judul = '';

    public string $isi = '';

    public string $target_type = 'semua';

    /** @var array<int,string> */
    public array $target_roles = [];

    /** @var array<int,int> */
    public array $target_rombels = [];

    public bool $penting = false;

    public string $tayang_dari = '';

    public string $tayang_sampai = '';

    public function mount(?Announcement $announcement = null): void
    {
        $this->authorize($announcement?->exists ? 'pengumuman.update' : 'pengumuman.create');

        if ($announcement?->exists) {
            $this->announcement = $announcement;
            $this->judul = $announcement->judul;
            $this->isi = $announcement->isi;
            $this->target_type = $announcement->target_type;
            /** @var array<string,mixed>|null $targetData */
            $targetData = $announcement->target_data;
            $this->target_roles = $targetData['roles'] ?? [];
            $this->target_rombels = $targetData['rombel_ids'] ?? [];
            $this->penting = $announcement->penting;
            /** @var Carbon $dari */
            $dari = $announcement->tayang_dari;
            $this->tayang_dari = $dari->toDateString();
            /** @var Carbon|null $sampai */
            $sampai = $announcement->tayang_sampai;
            $this->tayang_sampai = $sampai?->toDateString() ?? '';
        } else {
            $this->tayang_dari = now()->toDateString();
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:10000'],
            'target_type' => ['required', Rule::in(['semua', 'peran', 'kelas'])],
            'target_roles' => ['array'],
            'target_rombels' => ['array'],
            'penting' => ['boolean'],
            'tayang_dari' => ['required', 'date'],
            'tayang_sampai' => ['nullable', 'date', 'after_or_equal:tayang_dari'],
        ]);

        $targetData = match ($this->target_type) {
            'peran' => ['roles' => array_values($this->target_roles)],
            'kelas' => ['rombel_ids' => array_values($this->target_rombels)],
            default => null,
        };

        $payload = [
            'judul' => $data['judul'],
            'isi' => $data['isi'],
            'target_type' => $data['target_type'],
            'target_data' => $targetData,
            'penting' => $this->penting,
            'tayang_dari' => $data['tayang_dari'],
            'tayang_sampai' => $data['tayang_sampai'] ?: null,
            'dibuat_oleh' => auth()->id(),
        ];

        if ($this->announcement?->exists) {
            $this->announcement->update($payload);
            session()->flash('ok', 'Pengumuman diperbarui.');
        } else {
            Announcement::create($payload);
            session()->flash('ok', 'Pengumuman disimpan sebagai draf.');
        }

        $this->redirectRoute('pengumuman.index');
    }

    public function render(): View
    {
        return view('livewire.pengumuman.announcement-form', [
            'roles' => Role::where('guard_name', 'web')->orderBy('name')->pluck('name'),
            'rombels' => Rombel::orderBy('nama')->get(['id', 'nama']),
            'isEdit' => (bool) $this->announcement?->exists,
        ])->layout('layouts.app', ['title' => $this->announcement?->exists ? 'Ubah Pengumuman' : 'Buat Pengumuman']);
    }
}
