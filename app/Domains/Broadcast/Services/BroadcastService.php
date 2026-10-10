<?php

declare(strict_types=1);

namespace App\Domains\Broadcast\Services;

use App\Models\Broadcast;
use App\Models\Employee;
use App\Models\Rombel;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Pengiriman broadcast ke daftar penerima berdasarkan target.
 *
 * v1: pengiriman dicatat ke broadcast_logs. Channel WhatsApp
 * didelegasikan ke gateway eksternal (lihat Dok 11) — service ini
 * menyiapkan daftar penerima + status per penerima.
 */
class BroadcastService
{
    /**
     * Kirim broadcast sekarang; catat log per penerima.
     */
    public function kirim(Broadcast $broadcast): int
    {
        $penerima = $this->resolvePenerima($broadcast);

        $terkirim = 0;

        foreach ($penerima as $p) {
            $ok = $p['kontak'] !== null && $p['kontak'] !== '';

            $broadcast->logs()->create([
                'penerima' => $p['nama'],
                'status' => $ok ? 'terkirim' : 'gagal',
                'error' => $ok ? null : 'Tidak ada kontak tujuan.',
                'dikirim_pada' => now(),
            ]);

            if ($ok) {
                $terkirim++;
            }
        }

        $broadcast->update([
            'status' => $penerima->isEmpty() || $terkirim === 0 ? 'gagal' : 'terkirim',
        ]);

        return $terkirim;
    }

    /**
     * Proses semua broadcast terjadwal yang sudah jatuh tempo.
     *
     * @return int jumlah broadcast yang diproses
     */
    public function prosesTerjadwal(): int
    {
        $count = 0;

        Broadcast::where('status', 'terjadwal')
            ->whereNotNull('dijadwalkan_pada')
            ->where('dijadwalkan_pada', '<=', now())
            ->cursor()
            ->each(function (Broadcast $broadcast) use (&$count): void {
                $this->kirim($broadcast);
                $count++;
            });

        return $count;
    }

    /**
     * @return Collection<int, array{nama: string, kontak: ?string}>
     */
    public function resolvePenerima(Broadcast $broadcast): Collection
    {
        return match ($broadcast->target_type) {
            'siswa' => Student::where('status', 'aktif')
                ->orderBy('nama')
                ->get(['nama', 'phone'])
                ->map(fn (Student $s): array => ['nama' => $s->nama, 'kontak' => $s->phone]),
            'guru' => Employee::where('status_aktif', true)
                ->orderBy('nama')
                ->get(['nama', 'phone'])
                ->map(fn (Employee $e): array => ['nama' => $e->nama, 'kontak' => $e->phone]),
            'rombel' => $this->penerimaRombel($broadcast),
            default => $this->penerimaSiswa()->merge($this->penerimaGuru()),
        };
    }

    /**
     * @return Collection<int, array{nama: non-falsy-string, kontak: ?string}>
     */
    private function penerimaSiswa(): Collection
    {
        return Student::where('status', 'aktif')
            ->orderBy('nama')
            ->get(['nama', 'phone'])
            ->map(fn (Student $s): array => ['nama' => $s->nama.' (siswa)', 'kontak' => $s->phone]);
    }

    /**
     * @return Collection<int, array{nama: non-falsy-string, kontak: ?string}>
     */
    private function penerimaGuru(): Collection
    {
        return Employee::where('status_aktif', true)
            ->orderBy('nama')
            ->get(['nama', 'phone'])
            ->map(fn (Employee $e): array => ['nama' => $e->nama.' (pegawai)', 'kontak' => $e->phone]);
    }

    /**
     * @return Collection<int, array{nama: string, kontak: ?string}>
     */
    private function penerimaRombel(Broadcast $broadcast): Collection
    {
        /** @var array<string,mixed>|null $targetData */
        $targetData = $broadcast->target_data;
        /** @var array<int>|null $rombelIds */
        $rombelIds = $targetData['rombel_ids'] ?? null;

        $query = Student::where('status', 'aktif');

        if ($rombelIds !== null && $rombelIds !== []) {
            $studentIds = Rombel::whereIn('id', $rombelIds)
                ->with('students:id')
                ->get()
                ->flatMap(fn (Rombel $r) => $r->students->pluck('id'));

            $query->whereIn('id', $studentIds);
        }

        return $query->orderBy('nama')
            ->get(['nama', 'phone'])
            ->map(fn (Student $s): array => ['nama' => $s->nama, 'kontak' => $s->phone]);
    }
}
