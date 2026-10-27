<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\ClassOfficer;
use App\Models\Rombel;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Kelola anggota kelas: papan dua kolom dengan perubahan bertahap (FR-102).
 *
 * "Tambahkan"/"Keluarkan" hanya menahap perubahan di sisi server komponen
 * (stagedAdded/stagedRemoved); tidak ada tulis ke database sampai tombol
 * "Simpan perubahan" ditekan. Aturan yang dipertahankan: satu siswa satu
 * rombel aktif per tahun ajaran (ditegakkan juga oleh unique index
 * class_student_active_unique), dan penempatan lama tidak dihapus melainkan
 * berstatus "pindah". Daftar calon = siswa aktif yang belum menjadi anggota
 * kelas ini (termasuk yang sedang dihapus bertahap dari kelas ini); daftar
 * anggota = anggota kelas ditambah yang sedang ditahap masuk.
 */
class RombelShow extends Component
{
    public Rombel $rombel;

    public string $searchSiswa = '';

    public string $searchAnggota = '';

    /** Filter angkatan calon: "semua" atau tahun (mis. "2026"). */
    public string $filterAngkatan = 'semua';

    /** Sort: nama | nama_desc | nis | angkatan. */
    public string $sortCalon = 'nama';

    public string $sortAnggota = 'nama';

    /** @var array<int> */
    public array $selectedCalon = [];

    /** @var array<int> */
    public array $selectedAnggota = [];

    /** @var array<int> ID calon yang ditahap masuk (belum simpan). */
    public array $stagedAdded = [];

    /** @var array<int> ID anggota yang ditahap keluar (belum simpan). */
    public array $stagedRemoved = [];

    /** @var array<string, int|null> Jabatan pengurus => ID siswa (null bila kosong). */
    public array $pengurus = [
        'ketua' => null,
        'wakil' => null,
        'sekretaris' => null,
        'bendahara' => null,
    ];

    public int $calonPage = 1;

    public int $anggotaPage = 1;

    public bool $confirmKapasitas = false;

    public bool $konfirmasiKapasitas = false;

    public const PER_PAGE = 8;

    public function mount(Rombel $rombel): void
    {
        $this->rombel = $rombel->load(['waliKelas:id,nama', 'academicYear:id,nama']);
        $this->pengurus = $this->loadPengurus();
    }

    public function updatedSearchSiswa(): void
    {
        $this->calonPage = 1;
    }

    public function updatedFilterAngkatan(): void
    {
        $this->calonPage = 1;
    }

    public function updatedSortCalon(): void
    {
        $this->calonPage = 1;
    }

    public function updatedSearchAnggota(): void
    {
        $this->anggotaPage = 1;
    }

    public function updatedSortAnggota(): void
    {
        $this->anggotaPage = 1;
    }

    /**
     * Tahap masuk: calon yang dipilih pindah ke daftar anggota (belum simpan).
     * Bila ID sedang ditahap keluar, aksinya membatalkan pengeluaran itu.
     */
    public function tambahkan(): void
    {
        $this->authorize('kelas.update');

        $ids = $this->selectedValid($this->selectedCalon, $this->calonEligibleIds());
        foreach ($ids as $id) {
            if (in_array($id, $this->stagedRemoved, true)) {
                $this->stagedRemoved = array_values(array_diff($this->stagedRemoved, [$id]));
            } elseif (! in_array($id, $this->stagedAdded, true)) {
                $this->stagedAdded[] = $id;
            }
        }
        $this->selectedCalon = [];
    }

    /**
     * Tahap keluar: anggota yang dipilih pindah ke daftar calon (belum simpan).
     * Bila ID sedang ditahap masuk, aksinya membatalkan penambahan itu.
     */
    public function keluarkan(): void
    {
        $this->authorize('kelas.update');

        $ids = $this->selectedValid($this->selectedAnggota, $this->anggotaEligibleIds());
        foreach ($ids as $id) {
            if (in_array($id, $this->stagedAdded, true)) {
                $this->stagedAdded = array_values(array_diff($this->stagedAdded, [$id]));
            } elseif (! in_array($id, $this->stagedRemoved, true)) {
                $this->stagedRemoved[] = $id;
            }
        }
        $this->selectedAnggota = [];
    }

    /**
     * Simpan semua perubahan bertahap ke database dalam satu transaksi.
     */
    public function simpan(): void
    {
        $this->authorize('kelas.update');

        $added = $this->selectedValid($this->stagedAdded, $this->calonBaseIds());
        $removed = $this->selectedValid($this->stagedRemoved, $this->anggotaIds());

        if ($added === [] && $removed === []) {
            $this->resetStaging();

            return;
        }

        if (! $this->konfirmasiKapasitas && $this->kapasitasTerlampaui($added, $removed)) {
            $this->confirmKapasitas = true;

            return;
        }

        DB::transaction(function () use ($added, $removed): void {
            $this->tempatkan($added);
            $this->keluarkanDariKelas($removed);

            // Siswa yang keluar kelas kehilangan jabatannya di kelas ini.
            if ($removed !== []) {
                DB::table('class_officers')
                    ->where('rombel_id', $this->rombel->id)
                    ->where('academic_year_id', $this->rombel->academic_year_id)
                    ->whereIn('student_id', $removed)
                    ->delete();
            }
        });

        $this->resetStaging();
        $this->pengurus = $this->loadPengurus();
        $this->calonPage = 1;
        $this->anggotaPage = 1;

        $bagian = [];
        if ($added !== []) {
            $bagian[] = count($added).' siswa ditambahkan';
        }
        if ($removed !== []) {
            $bagian[] = count($removed).' siswa dikeluarkan';
        }
        session()->flash('sukses', 'Perubahan anggota kelas disimpan: '.implode(', ', $bagian).'.');
    }

    /**
     * Lanjutkan simpan walau kelas melebihi kapasitas.
     */
    public function konfirmasiSimpan(): void
    {
        $this->authorize('kelas.update');

        $this->konfirmasiKapasitas = true;
        $this->confirmKapasitas = false;
        $this->simpan();
    }

    public function batalKonfirmasi(): void
    {
        $this->confirmKapasitas = false;
    }

    /**
     * Simpan pengurus kelas. Pengurus hanya boleh dipilih dari anggota kelas
     * di database (bukan dari tahap), dan satu siswa memegang satu jabatan.
     */
    public function simpanPengurus(): void
    {
        $this->authorize('kelas.update');

        $members = $this->anggotaIds();
        $values = [];
        $hasError = false;

        foreach (ClassOfficer::JABATAN as $key => $label) {
            $raw = $this->pengurus[$key] ?? null;
            $id = $raw === '' || $raw === null ? null : (int) $raw;

            if ($id !== null && ! in_array($id, $members, true)) {
                $this->addError('pengurus.'.$key, $label.' harus dipilih dari anggota kelas ini.');
                $hasError = true;
            }
            $values[$key] = $id;
        }

        $filled = array_values(array_filter($values));
        if (count($filled) !== count(array_unique($filled))) {
            $this->addError('pengurus.ketua', 'Satu siswa hanya boleh memegang satu jabatan.');
            $hasError = true;
        }

        if ($hasError) {
            return;
        }

        DB::transaction(function () use ($values): void {
            ClassOfficer::query()
                ->where('rombel_id', $this->rombel->id)
                ->where('academic_year_id', $this->rombel->academic_year_id)
                ->delete();

            foreach ($values as $jabatan => $studentId) {
                if ($studentId !== null) {
                    ClassOfficer::create([
                        'rombel_id' => $this->rombel->id,
                        'student_id' => $studentId,
                        'academic_year_id' => $this->rombel->academic_year_id,
                        'jabatan' => $jabatan,
                    ]);
                }
            }
        });

        $this->pengurus = $values;
        session()->flash('sukses', 'Pengurus kelas disimpan.');
    }

    public function gotoCalonPage(int $page): void
    {
        $this->calonPage = max(1, $page);
    }

    public function gotoAnggotaPage(int $page): void
    {
        $this->anggotaPage = max(1, $page);
    }

    /**
     * Inisial avatar (dua huruf pertama dari dua kata pertama nama).
     */
    public function initials(string $nama): string
    {
        $words = preg_split('/\s+/', trim($nama)) ?: [];
        $first = $words[0] ?? '';
        $second = $words[1] ?? '';

        return mb_strtoupper(mb_substr($first, 0, 1).($second !== '' ? mb_substr($second, 0, 1) : ''));
    }

    /**
     * Halaman pager: nomor halaman + null sebagai elipsis.
     *
     * @return array<int|null>
     */
    public function pagerPages(int $current, int $last): array
    {
        if ($last <= 7) {
            return range(1, $last);
        }

        $pages = array_filter([1, $last, $current - 1, $current, $current + 1], fn ($p) => $p >= 1 && $p <= $last);
        sort($pages);
        $pages = array_values(array_unique($pages));

        $out = [];
        $prev = null;
        foreach ($pages as $p) {
            if ($prev !== null && $p > $prev + 1) {
                $out[] = null;
            }
            $out[] = $p;
            $prev = $p;
        }

        return $out;
    }

    public function render(): View
    {
        $calon = $this->calonQuery()->paginate(self::PER_PAGE, ['*'], 'calon_page', $this->calonPage);
        if ($this->calonPage > $calon->lastPage()) {
            $this->calonPage = max(1, $calon->lastPage());
            $calon = $this->calonQuery()->paginate(self::PER_PAGE, ['*'], 'calon_page', $this->calonPage);
        }

        $anggota = $this->anggotaQuery()->paginate(self::PER_PAGE, ['*'], 'anggota_page', $this->anggotaPage);
        if ($this->anggotaPage > $anggota->lastPage()) {
            $this->anggotaPage = max(1, $anggota->lastPage());
            $anggota = $this->anggotaQuery()->paginate(self::PER_PAGE, ['*'], 'anggota_page', $this->anggotaPage);
        }

        $angkatanList = Student::where('status', 'aktif')
            ->whereNotNull('angkatan')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan')
            ->all();

        // Kelas asal calon di tahun ajaran ini (satu kueri untuk semua baris).
        $asalMap = DB::table('class_student')
            ->join('rombels', 'rombels.id', '=', 'class_student.rombel_id')
            ->whereIn('class_student.student_id', $calon->getCollection()->pluck('id')->all())
            ->where('class_student.academic_year_id', $this->rombel->academic_year_id)
            ->where('class_student.status', 'aktif')
            ->pluck('rombels.nama', 'class_student.student_id')
            ->all();

        return view('livewire.akademik.rombel-show', [
            'calon' => $calon,
            'anggota' => $anggota,
            'jumlahAnggota' => $this->stagedTotal(),
            'angkatanList' => $angkatanList,
            'asalMap' => $asalMap,
            'pengurusAnggota' => Student::whereIn('id', $this->anggotaIds())->orderBy('nama')->get(['id', 'nama']),
            'pengurusLabels' => ClassOfficer::JABATAN,
            'dirty' => $this->stagedAdded !== [] || $this->stagedRemoved !== [],
            'calonPager' => $this->pagerPages($calon->currentPage(), $calon->lastPage()),
            'anggotaPager' => $this->pagerPages($anggota->currentPage(), $anggota->lastPage()),
        ])->layout('layouts.app', ['title' => 'Kelas '.$this->rombel->nama]);
    }

    /**
     * Pengurus tersimpan di database (jabatan => ID siswa, null bila kosong).
     *
     * @return array<string, int|null>
     */
    private function loadPengurus(): array
    {
        $map = ClassOfficer::query()
            ->where('rombel_id', $this->rombel->id)
            ->where('academic_year_id', $this->rombel->academic_year_id)
            ->pluck('student_id', 'jabatan')
            ->all();

        $out = [];
        foreach (ClassOfficer::JABATAN as $key => $label) {
            $out[$key] = isset($map[$key]) ? (int) $map[$key] : null;
        }

        return $out;
    }

    /**
     * ID anggota kelas di database (tanpa tahap) untuk tahun ajaran rombel.
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
     * ID calon di database (siswa aktif yang belum menjadi anggota kelas ini).
     *
     * @return array<int>
     */
    private function calonBaseIds(): array
    {
        return Student::query()
            ->where('status', 'aktif')
            ->whereNotIn('id', $this->anggotaIds())
            ->pluck('id')
            ->all();
    }

    /**
     * ID yang sah ditahap masuk (calon database + yang sedang ditahap keluar).
     *
     * @return array<int>
     */
    private function calonEligibleIds(): array
    {
        $ids = $this->calonBaseIds();
        foreach ($this->stagedRemoved as $id) {
            if (! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return array_values(array_diff($ids, $this->stagedAdded));
    }

    /**
     * ID yang sah ditahap keluar (anggota database + yang sedang ditahap masuk).
     *
     * @return array<int>
     */
    private function anggotaEligibleIds(): array
    {
        $ids = $this->anggotaIds();
        foreach ($this->stagedAdded as $id) {
            if (! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return array_values(array_diff($ids, $this->stagedRemoved));
    }

    /**
     * Total anggota setelah tahap diterapkan (untuk kartu kapasitas).
     */
    private function stagedTotal(): int
    {
        $added = $this->selectedValid($this->stagedAdded, $this->calonBaseIds());
        $removed = $this->selectedValid($this->stagedRemoved, $this->anggotaIds());

        return count($this->anggotaIds()) - count($removed) + count($added);
    }

    /**
     * Kueri calon siap tampil (dengan pencarian, filter angkatan, sortir).
     */
    private function calonQuery(): Builder
    {
        $members = $this->anggotaIds();
        $query = Student::query()->where('status', 'aktif');

        $query->where(function (Builder $q) use ($members): void {
            $q->whereNotIn('id', $members);
            if ($this->stagedRemoved !== []) {
                $q->orWhereIn('id', $this->stagedRemoved);
            }
        });
        if ($this->stagedAdded !== []) {
            $query->whereNotIn('id', $this->stagedAdded);
        }

        if ($this->searchSiswa !== '') {
            $term = '%'.$this->searchSiswa.'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('nama', 'like', $term)
                    ->orWhere('nis', 'like', $term)
                    ->orWhere('nisn', 'like', $term);
            });
        }

        if ($this->filterAngkatan !== 'semua') {
            $query->where('angkatan', (int) $this->filterAngkatan);
        }

        $this->applySort($query, $this->sortCalon);

        return $query;
    }

    /**
     * Kueri anggota siap tampil (dengan pencarian dan sortir).
     */
    private function anggotaQuery(): Builder
    {
        $members = $this->anggotaIds();
        $query = Student::query();

        $query->where(function (Builder $q) use ($members): void {
            $q->whereIn('id', $members);
            if ($this->stagedAdded !== []) {
                $q->orWhereIn('id', $this->stagedAdded);
            }
        });
        if ($this->stagedRemoved !== []) {
            $query->whereNotIn('id', $this->stagedRemoved);
        }

        if ($this->searchAnggota !== '') {
            $term = '%'.$this->searchAnggota.'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('nama', 'like', $term)
                    ->orWhere('nis', 'like', $term)
                    ->orWhere('nisn', 'like', $term);
            });
        }

        $this->applySort($query, $this->sortAnggota);

        return $query;
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'nama_desc' => $query->orderByDesc('nama'),
            'nis' => $query->orderBy('nis')->orderBy('nama'),
            'angkatan' => $query->orderByRaw('CASE WHEN angkatan IS NULL THEN 1 ELSE 0 END, angkatan')->orderBy('nama'),
            default => $query->orderBy('nama'),
        };
    }

    /**
     * Bersihkan seleksi: hanya ID yang masih sah, unik, dan integer.
     *
     * @param  array<int|string>  $selected
     * @param  array<int>  $eligible
     * @return array<int>
     */
    private function selectedValid(array $selected, array $eligible): array
    {
        $selected = array_map('intval', $selected);

        return array_values(array_intersect($selected, $eligible));
    }

    /**
     * Apakah total anggota bertahap melampaui kapasitas kelas.
     *
     * @param  array<int>  $added
     * @param  array<int>  $removed
     */
    private function kapasitasTerlampaui(array $added, array $removed): bool
    {
        if ($this->rombel->kapasitas === null) {
            return false;
        }

        return count($this->anggotaIds()) - count($removed) + count($added) > $this->rombel->kapasitas;
    }

    private function resetStaging(): void
    {
        $this->stagedAdded = [];
        $this->stagedRemoved = [];
        $this->selectedCalon = [];
        $this->selectedAnggota = [];
        $this->confirmKapasitas = false;
        $this->konfirmasiKapasitas = false;
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
        if ($studentIds === []) {
            return;
        }

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
    }

    /**
     * @param  array<int>  $studentIds
     */
    private function keluarkanDariKelas(array $studentIds): void
    {
        if ($studentIds === []) {
            return;
        }

        DB::table('class_student')
            ->where('rombel_id', $this->rombel->id)
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $this->rombel->academic_year_id)
            ->update(['status' => 'pindah']);
    }
}
