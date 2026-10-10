<?php

declare(strict_types=1);

namespace App\Livewire\Broadcast;

use App\Models\Broadcast;
use App\Models\Rombel;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form buat/ubah broadcast.
 */
class BroadcastForm extends Component
{
    public ?Broadcast $broadcast = null;

    public string $judul = '';

    public string $pesan = '';

    public string $channel = 'notifikasi';

    public string $target_type = 'semua';

    /** @var array<int> */
    public array $rombel_ids = [];

    public string $dijadwalkan_pada = '';

    public function mount(?Broadcast $broadcast = null): void
    {
        $this->authorize($broadcast?->exists ? 'broadcast.update' : 'broadcast.create');

        if ($broadcast?->exists) {
            $this->broadcast = $broadcast;
            $this->judul = $broadcast->judul;
            $this->pesan = $broadcast->pesan;
            $this->channel = $broadcast->channel;
            $this->target_type = $broadcast->target_type;
            /** @var array<string,mixed>|null $targetData */
            $targetData = $broadcast->target_data;
            /** @var array<int> $ids */
            $ids = $targetData['rombel_ids'] ?? [];
            $this->rombel_ids = $ids;
            /** @var Carbon|null $jadwal */
            $jadwal = $broadcast->dijadwalkan_pada;
            $this->dijadwalkan_pada = $jadwal?->format('Y-m-d\TH:i') ?? '';
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'judul' => ['required', 'string', 'max:200'],
            'pesan' => ['required', 'string', 'max:5000'],
            'channel' => ['required', Rule::in(['whatsapp', 'notifikasi'])],
            'target_type' => ['required', Rule::in(['semua', 'siswa', 'guru', 'rombel'])],
            'rombel_ids' => ['array'],
            'rombel_ids.*' => ['integer', 'exists:rombels,id'],
            'dijadwalkan_pada' => ['nullable', 'date'],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = [
            'judul' => $data['judul'],
            'pesan' => $data['pesan'],
            'channel' => $data['channel'],
            'target_type' => $data['target_type'],
            'target_data' => $data['target_type'] === 'rombel' ? ['rombel_ids' => $data['rombel_ids']] : null,
            'dijadwalkan_pada' => $data['dijadwalkan_pada'] ?: null,
            'dibuat_oleh' => auth()->id(),
        ];

        if ($this->broadcast?->exists) {
            abort_if(! in_array($this->broadcast->status, ['draf', 'terjadwal'], true), 422, 'Broadcast yang sudah terkirim tidak bisa diubah.');

            $payload['status'] = $payload['dijadwalkan_pada'] ? 'terjadwal' : 'draf';
            $this->broadcast->update($payload);
            session()->flash('ok', 'Broadcast diperbarui.');
        } else {
            $payload['status'] = $payload['dijadwalkan_pada'] ? 'terjadwal' : 'draf';
            Broadcast::create($payload);
            session()->flash('ok', 'Broadcast dibuat.');
        }

        $this->redirectRoute('broadcast.index');
    }

    public function render(): View
    {
        return view('livewire.broadcast.broadcast-form', [
            'rombels' => Rombel::orderBy('nama')->get(['id', 'nama']),
            'isEdit' => (bool) $this->broadcast?->exists,
        ])->layout('layouts.app', ['title' => $this->broadcast?->exists ? 'Ubah Broadcast' : 'Buat Broadcast']);
    }
}
