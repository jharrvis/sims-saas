<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\Rombel;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Detail rombel: papan manajemen kelas dua kolom (FR-102).
 *
 * Kolom kiri = calon siswa (aktif, belum di kelas ini, tahun ajaran rombel);
 * kolom kanan = anggota kelas. Pindah massal lewat checkbox + tombol tengah.
 * Aturan yang dipertahankan: satu siswa satu rombel aktif per tahun ajaran
 * (ditegakkan juga oleh unique index class_student_active_unique), dan
 * penempatan lama tidak dihapus melainkan berstatus "pindah".
 */
class RombelShow extends Component
{
    public Rombel $rombel;

    public string $searchSiswa = '';

    public string $searchAnggota = '';

    /** Filter asal calon: "semua" | "tanpa-kelas" | "rombel:<id>". */
    public string $filterAsal = 'semua';

    /** Sort calon: nama | nis | angkatan | kelas_asal. */
    public string $sortCalon = 'nama';

    /** Sort anggota: nama | nis | angkatan. */
    public string $sortAnggota = 'nama';

    /** @var array<int> */
    public array $selectedCalon = [];

    /** @var array<int> */
    public array $selectedAnggota = [];

    public bool $confirmKapasitas = false;

    /** @var array<int> ID calon yang menunggu konfirmasi kapasitas. */
    public array $pendingAssign = [];

    /** Batas tampil calon agar daftar tidak membebani render. */
    public const CALON_LIMIT = 100;

    public function mount(Rombel $rombel): void
    {
        $this->rombel = $rombel->load(['waliKelas:id,nama', 'academicYear:id,nama']);
    }

    /**
     * Tempatkan satu siswa ke rombel ini (FR-102).
     */
    public function assignStudent(int $studentId): void
    {
        $this->authorize('kelas.update');

        if ($this->kapasitasTerlampaui([$studentId])) {
            $this->pendingAssign = [$studentId];
            $this->confirmKapasitas = true;

            return;
        }

        $student = Student::findOrFail($studentId);
        $this->tempatkan([$student->id]);

        session()->flash('sukses', "Siswa {$student->nama} ditempatkan ke kelas {$this->rombel->nama}.");
    }

    /**
     * Tempatkan semua calon yang dipilih.
     */
    public function assignSelected(): void
    {
        $this->authorize('kelas.update');

        $ids = $this->selectedValid($this->selectedCalon, $this->calonDisplayIds());
        if ($ids === []) {
            return;
        }

        if ($this->kapasitasTerlampaui($ids)) {
            $this->pendingAssign = $ids;
            $this->confirmKapasitas = true;

            return;
        }

        $this->tempatkan($ids);
        $this->selectedCalon = [];

        session()->flash('sukses', count($ids).' siswa ditempatkan ke kelas '.$this->rombel->nama.'.');
    }

    /**
     * Lanjutkan penempatan yang tertunda setelah konfirmasi kapasitas.
     */
    public function confirmPendingAssign(): void
    {
        $this->authorize('kelas.update');

        $ids = $this->selectedValid($this->pendingAssign, $this->calonDisplayIds());
        $this->pendingAssign = [];
        $this->confirmKapasitas = false;

        if ($ids === []) {
            return;
        }

        $this->tempatkan($ids);
        $this->selectedCalon = array_values(array_diff($this->selectedCalon, $ids));

        session()->flash('sukses', count($ids).' siswa ditempatkan ke kelas '.$this->rombel->nama.'.');
    }

    public function cancelPendingAssign(): void
    {
        $this->pendingAssign = [];
        $this->confirmKapasitas = false;
    }

    public function removeStudent(int $studentId): void
    {
        $this->authorize('kelas.update');

        $this->keluarkan([$studentId]);

        session()->flash('sukses', 'Siswa dikeluarkan dari kelas '.$this->rombel->nama.'.');
    }

    /**
     * Keluarkan semua anggota yang dipilih.
     */
    public function removeSelected(): void
    {
        $this->authorize('kelas.update');

        $ids = $this->selectedValid($this->selectedAnggota, $this->anggotaDisplayIds());
        if ($ids === []) {
            return;
        }

        $this->keluarkan($ids);
        $this->selectedAnggota = [];

        session()->flash('sukses', count($ids).' siswa dikeluarkan dari kelas '.$this->rombel->nama.'.');
    }

    /**
     * Pilih/batalkan semua baris yang sedang ditampilkan di satu kolom.
     */
    public function toggleSelectAllCalon(): void
    {
        $ids = $this->calonDisplayIds();
        $this->selectedCalon = $this->semuaDipilih($ids, $this->selectedCalon)
            ? array_values(array_diff($this->selectedCalon, $ids))
            : array_values(array_unique(array_merge($this->selectedCalon, $ids)));
    }

    public function toggleSelectAllAnggota(): void
    {
        $ids = $this->anggotaDisplayIds();
        $this->selectedAnggota = $this->semuaDipilih($ids, $this->selectedAnggota)
            ? array_values(array_diff($this->selectedAnggota, $ids))
            : array_values(array_unique(array_merge($this->selectedAnggota, $ids)));
    }

    /**
     * Apakah semua ID yang ditampilkan sudah dipilih.
     */
    private function semuaDipilih(array $ids, array $selected): bool
    {
        return $ids !== [] && array_diff($ids, $selected) === [];
    }

    public function render(): View
    {
        $anggotaIds = $this->anggotaIds();

        $siswa = $this->anggotaDisplayQuery()->get();

        $calonTotal = $this->calonBaseQuery()->count();
        $calon = $this->calonDisplayQuery()->get();

        // Kelas asal calon di tahun ajaran ini (satu kueri untuk semua baris).
        $asalMap = DB::table('class_student')
            ->join('rombels', 'rombels.id', '=', 'class_student.rombel_id')
            ->whereIn('class_student.student_id', $calon->pluck('id')->all())
            ->where('class_student.academic_year_id', $this->rombel->academic_year_id)
            ->where('class_student.status', 'aktif')
            ->pluck('rombels.nama', 'class_student.student_id')
            ->all();

        $rombelAsal = Rombel::where('academic_year_id', $this->rombel->academic_year_id)
            ->whereKeyNot($this->rombel->id)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('livewire.akademik.rombel-show', [
            'siswa' => $siswa,
            'jumlahAnggota' => count($anggotaIds),
            'calon' => $calon,
            'calonTotal' => $calonTotal,
            'calonTerpotong' => $calonTotal > count($calon),
            'asalMap' => $asalMap,
            'rombelAsal' => $rombelAsal,
        ])->layout('layouts.app', ['title' => 'Kelas '.$this->rombel->nama]);
    }

    /**
     * ID anggota penuh (tanpa filter pencarian) untuk mengecualikan calon:
     * memakai daftar anggota yang sudah terfilter akan membuat anggota
     * muncul kembali sebagai calon saat pencarian anggota tidak cocok.
     *
     * @return array<int>
     */
    private function anggotaIds(): array
    {
        return $this->rombel->students()
            ->wherePivot('academic_year_id', $this->rombel->academic_year_id)
            ->wherePivot('status', 'aktif')
            ->pluck('students.id')
            ->all();
    }

    /**
     * Kueri dasar calon: siswa aktif yang belum menjadi anggota kelas ini.
     */
    private function calonBaseQuery(): Builder
    {
        return Student::query()
            ->where('status', 'aktif')
            ->whereNotIn('id', $this->anggotaIds());
    }

    /**
     * Terapkan pencarian (nama/NIS/NISN) dan filter asal ke kueri calon.
     */
    private function calonFilteredQuery(): Builder
    {
        $query = $this->calonBaseQuery();
        $yearId = $this->rombel->academic_year_id;

        if ($this->searchSiswa !== '') {
            $term = '%'.$this->searchSiswa.'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('nama', 'like', $term)
                    ->orWhere('nis', 'like', $term)
                    ->orWhere('nisn', 'like', $term);
            });
        }

        if ($this->filterAsal === 'tanpa-kelas') {
            $query->whereNotExists(function ($q) use ($yearId): void {
                $q->select(DB::raw(1))
                    ->from('class_student')
                    ->whereColumn('class_student.student_id', 'students.id')
                    ->where('class_student.academic_year_id', $yearId)
                    ->where('class_student.status', 'aktif');
            });
        } elseif (str_starts_with($this->filterAsal, 'rombel:')) {
            $asal = Rombel::whereKey((int) substr($this->filterAsal, 7))
                ->where('academic_year_id', $yearId)
                ->first();
            if ($asal !== null) {
                $query->whereExists(function ($q) use ($yearId, $asal): void {
                    $q->select(DB::raw(1))
                        ->from('class_student')
                        ->whereColumn('class_student.student_id', 'students.id')
                        ->where('class_student.academic_year_id', $yearId)
                        ->where('class_student.rombel_id', $asal->id)
                        ->where('class_student.status', 'aktif');
                });
            }
        }

        return $query;
    }

    /**
     * Kueri calon siap tampil (dengan sortir dan batas tampil).
     */
    private function calonDisplayQuery(): Builder
    {
        $query = $this->calonFilteredQuery();

        match ($this->sortCalon) {
            'nis' => $query->orderBy('nis')->orderBy('nama'),
            'angkatan' => $query->orderByRaw('CASE WHEN angkatan IS NULL THEN 1 ELSE 0 END, angkatan')->orderBy('nama'),
            'kelas_asal' => $query
                ->select('students.*')
                ->selectSub(
                    DB::table('class_student')
                        ->join('rombels', 'rombels.id', '=', 'class_student.rombel_id')
                        ->select('rombels.nama')
                        ->whereColumn('class_student.student_id', 'students.id')
                        ->where('class_student.academic_year_id', $this->rombel->academic_year_id)
                        ->where('class_student.status', 'aktif')
                        ->limit(1),
                    'asal_nama'
                )
                ->orderByRaw('CASE WHEN asal_nama IS NULL THEN 1 ELSE 0 END, asal_nama')
                ->orderBy('nama'),
            default => $query->orderBy('nama'),
        };

        return $query->limit(self::CALON_LIMIT);
    }

    /**
     * Kueri anggota siap tampil (dengan pencarian dan sortir).
     */
    private function anggotaDisplayQuery(): BelongsToMany
    {
        $query = $this->rombel->students()
            ->wherePivot('academic_year_id', $this->rombel->academic_year_id)
            ->wherePivot('status', 'aktif');

        if ($this->searchAnggota !== '') {
            $term = '%'.$this->searchAnggota.'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('students.nama', 'like', $term)
                    ->orWhere('students.nis', 'like', $term)
                    ->orWhere('students.nisn', 'like', $term);
            });
        }

        match ($this->sortAnggota) {
            'nis' => $query->orderBy('students.nis')->orderBy('students.nama'),
            'angkatan' => $query->orderByRaw('CASE WHEN students.angkatan IS NULL THEN 1 ELSE 0 END, students.angkatan')->orderBy('students.nama'),
            default => $query->orderBy('students.nama'),
        };

        return $query;
    }

    /**
     * @return array<int>
     */
    private function calonDisplayIds(): array
    {
        return $this->calonDisplayQuery()->pluck('students.id')->all();
    }

    /**
     * @return array<int>
     */
    private function anggotaDisplayIds(): array
    {
        return $this->anggotaDisplayQuery()->pluck('students.id')->all();
    }

    /**
     * Bersihkan seleksi: hanya ID yang masih tampil, unik, dan integer.
     *
     * @param  array<int|string>  $selected
     * @param  array<int>  $displayed
     * @return array<int>
     */
    private function selectedValid(array $selected, array $displayed): array
    {
        $selected = array_map('intval', $selected);

        return array_values(array_intersect($selected, $displayed));
    }

    /**
     * Apakah penempatan ID-ID ini akan melampaui kapasitas kelas.
     */
    private function kapasitasTerlampaui(array $ids): bool
    {
        if ($this->rombel->kapasitas === null) {
            return false;
        }

        return count($this->anggotaIds()) + count($ids) > $this->rombel->kapasitas;
    }

    /**
     * Pindahkan siswa ke rombel ini: nonaktifkan penempatan lama di tahun
     * ajaran yang sama, lalu pasang penempatan aktif. Semua penonaktifan
     * berjalan dulu agar unique index penempatan aktif tidak dilanggar.
     *
     * @param  array<int>  $studentIds
     */
    private function tempatkan(array $studentIds): void
    {
        DB::transaction(function () use ($studentIds): void {
            DB::table('class_student')
                ->whereIn('student_id', $studentIds)
                ->where('academic_year_id', $this->rombel->academic_year_id)
                ->update(['status' => 'pindah']);

            foreach ($studentIds as $studentId) {
                $this->rombel->students()->syncWithoutDetaching([
                    $studentId => [
                        'academic_year_id' => $this->rombel->academic_year_id,
                        'status' => 'aktif',
                    ],
                ]);
            }
        });
    }

    /**
     * @param  array<int>  $studentIds
     */
    private function keluarkan(array $studentIds): void
    {
        DB::table('class_student')
            ->where('rombel_id', $this->rombel->id)
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $this->rombel->academic_year_id)
            ->update(['status' => 'pindah']);
    }
}
