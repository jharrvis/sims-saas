<?php

declare(strict_types=1);

namespace App\Domains\Kesiswaan\Services;

use App\Domains\Impor\Services\BaseImporter;
use App\Domains\Platform\Services\QuotaService;
use App\Models\AcademicYear;
use App\Models\ImportBatch;
use App\Models\Rombel;
use App\Models\Student;
use App\Tenancy\TenantContext;

/**
 * Impor massal siswa dari .xlsx (US-012).
 * Header: nama, nis, jenis_kelamin, tanggal_lahir, kelas
 */
class StudentImporter extends BaseImporter
{
    public function headers(): array
    {
        return ['nama', 'nis', 'jenis_kelamin', 'tanggal_lahir', 'kelas'];
    }

    public function templateRows(): array
    {
        return [
            ['Ahmad Fauzi', '1001', 'L', '2012-05-10', 'VII A'],
            ['Siti Aminah', '1002', 'P', '2012-08-22', 'VII A'],
        ];
    }

    public function validateRow(array $row, int $line): array
    {
        $errors = [];
        [$nama, $nis, $jk, $tglLahir, $kelas] = $row;
        $schoolId = app(TenantContext::class)->requireSchoolId();

        if (! $nama) {
            $errors[] = "Baris {$line}: nama wajib diisi.";
        } elseif (mb_strlen($nama) > 150) {
            $errors[] = "Baris {$line}: nama maksimal 150 karakter.";
        }

        if (! $nis) {
            $errors[] = "Baris {$line}: NIS wajib diisi.";
        } elseif ($this->sudahAda('nis', $nis)) {
            $errors[] = "Baris {$line}: NIS {$nis} duplikat dalam file.";
        } elseif (Student::withTrashed()->where('school_id', $schoolId)->where('nis', $nis)->exists()) {
            // withTrashed: NIS siswa di sampah tetap memblokir (unique index DB
            // mencakup baris soft-deleted) — laporkan sebagai error validasi
            // yang jelas, bukan crash constraint saat eksekusi.
            $errors[] = "Baris {$line}: NIS {$nis} sudah terdaftar (termasuk data di sampah).";
        }

        if (! in_array(strtoupper((string) $jk), ['L', 'P'], true)) {
            $errors[] = "Baris {$line}: jenis_kelamin harus L atau P.";
        }

        if ($tglLahir && $this->parseDate($tglLahir) === null) {
            $errors[] = "Baris {$line}: tanggal_lahir tidak valid (gunakan YYYY-MM-DD).";
        }

        // Kolom kelas: bila belum ada rombel dengan nama tsb di tahun ajaran
        // aktif, rombel otomatis dibuat saat eksekusi. Tanpa tahun ajaran
        // aktif, kelas tidak bisa diproses.
        if ($kelas && ! AcademicYear::where('school_id', $schoolId)->where('aktif', true)->exists()) {
            $errors[] = "Baris {$line}: belum ada tahun ajaran aktif — buat dulu sebelum impor dengan kolom kelas.";
        }

        return $errors;
    }

    public function persistRow(array $row, ImportBatch $batch): void
    {
        [$nama, $nis, $jk, $tglLahir, $kelas] = $row;

        $student = Student::create([
            'school_id' => $batch->school_id,
            'import_batch_id' => $batch->id,
            'nama' => $nama,
            'nis' => $nis,
            'jk' => strtoupper((string) $jk),
            'tgl_lahir' => $this->parseDate($tglLahir),
            'status' => 'aktif',
        ]);

        if ($kelas && ($rombel = $this->findOrCreateRombel($kelas, $batch->school_id))) {
            $taId = AcademicYear::where('school_id', $batch->school_id)->where('aktif', true)->value('id');

            $student->rombels()->attach($rombel->id, [
                'academic_year_id' => $taId,
                'status' => 'aktif',
            ]);
        }
    }

    public function guardExecute(int $validCount): ?string
    {
        if ($validCount === 0) {
            return 'Tidak ada baris valid untuk diimpor.';
        }

        $quota = app(QuotaService::class)->check('siswa_aktif');

        if ($quota['batas'] !== null && $quota['batas'] < $quota['terpakai'] + $validCount) {
            return "Kuota siswa tidak cukup (terpakai {$quota['terpakai']}/{$quota['batas']}, butuh {$validCount}).";
        }

        return null;
    }

    private function findRombel(?string $nama): ?Rombel
    {
        if (! $nama) {
            return null;
        }

        $schoolId = app(TenantContext::class)->requireSchoolId();
        $taId = AcademicYear::where('school_id', $schoolId)->where('aktif', true)->value('id');

        return Rombel::where('school_id', $schoolId)
            ->when($taId, fn ($q) => $q->where('academic_year_id', $taId))
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower(trim($nama))])
            ->first();
    }

    /**
     * Cari rombel berdasarkan nama; bila belum ada, buat otomatis di tahun
     * ajaran aktif (impor siswa tidak lagi mewajibkan kelas dibuat dulu).
     */
    private function findOrCreateRombel(string $nama, int $schoolId): ?Rombel
    {
        $nama = trim($nama);
        $taId = AcademicYear::where('school_id', $schoolId)->where('aktif', true)->value('id');

        if (! $taId) {
            return null;
        }

        $rombel = Rombel::where('school_id', $schoolId)
            ->where('academic_year_id', $taId)
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->first();

        if (! $rombel) {
            // Tingkat ditebak dari kata pertama nama kelas ("VII A" → "VII").
            $tingkat = mb_strtoupper(explode(' ', $nama)[0] ?? '');

            $rombel = Rombel::create([
                'school_id' => $schoolId,
                'academic_year_id' => $taId,
                'tingkat' => $tingkat !== '' ? $tingkat : '-',
                'nama' => $nama,
            ]);
        }

        return $rombel;
    }
}
