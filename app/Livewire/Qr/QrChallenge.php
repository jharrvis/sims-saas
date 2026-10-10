<?php

declare(strict_types=1);

namespace App\Livewire\Qr;

use App\Domains\Pwa\Services\QrLoginService;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Tantangan QR dinamis untuk layar kelas (Dok 09 §3.3):
 * 128-bit, sekali pakai, 60 detik, refresh otomatis.
 */
class QrChallenge extends Component
{
    public string $search = '';

    public ?int $student_id = null;

    public ?string $token = null;

    public ?string $expires_at = null;

    public ?string $student_nama = null;

    public function buat(QrLoginService $qr): void
    {
        $this->authorize('qr.manage');
        $this->validate(['student_id' => ['required', 'integer', 'exists:students,id']]);

        $student = Student::findOrFail($this->student_id);
        $hasil = $qr->createChallenge(auth()->user(), $student);

        $this->token = $hasil['token'];
        $this->expires_at = $hasil['expires_at']->toDateTimeString();
        $this->student_nama = $student->nama;
    }

    public function refresh(QrLoginService $qr): void
    {
        // Dipanggil via wire:poll: jika kedaluwarsa, buat baru otomatis.
        if ($this->token !== null && $this->expires_at !== null
            && CarbonImmutable::parse($this->expires_at)->isPast()
        ) {
            $this->buat($qr);
        }
    }

    public function pilihSiswa(int $id): void
    {
        $this->student_id = $id;
        $found = Student::find($id);
        $this->search = $found !== null ? $found->nama : '';
    }

    public function render(): View
    {
        $this->authorize('qr.manage');

        $hasil = $this->search !== '' && $this->student_id === null
            ? Student::where('status', 'aktif')
                ->where(fn ($q) => $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nis', 'like', '%'.$this->search.'%'))
                ->limit(8)->get(['id', 'nama', 'nis'])
            : collect();

        return view('livewire.qr.qr-challenge', ['hasil' => $hasil])
            ->layout('layouts.app', ['title' => 'Tantangan QR']);
    }
}
