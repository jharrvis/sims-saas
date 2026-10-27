<?php

declare(strict_types=1);

namespace App\Livewire\Akademik;

use App\Models\ClassOfficer;
use App\Models\Rombel;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Halaman lihat kelas (baca saja): kartu info + daftar anggota kelas.
 *
 * Pengubahan anggota tidak ada di sini; tombol "Atur anggota kelas" (hanya
 * pemegang kelas.update) menuju papan kelola RombelShow di rute kelas.anggota.
 */
class RombelDetail extends Component
{
    public Rombel $rombel;

    public string $searchAnggota = '';

    /** Sort: nama | nama_desc | nis | angkatan. */
    public string $sortAnggota = 'nama';

    public int $anggotaPage = 1;

    /** Jumlah baris per halaman; 0 berarti tampilkan semua. */
    public int $perPage = 8;

    public const PER_PAGE_OPTIONS = [8, 16, 32, 50];

    /** ID siswa yang modal detailnya sedang dibuka (null bila tidak ada). */
    public ?int $detailSiswaId = null;

    public function mount(Rombel $rombel): void
    {
        $this->rombel = $rombel->load(['waliKelas:id,nama', 'academicYear:id,nama']);
    }

    public function updatedSearchAnggota(): void
    {
        $this->anggotaPage = 1;
    }

    public function updatedSortAnggota(): void
    {
        $this->anggotaPage = 1;
    }

    public function updatedPerPage(): void
    {
        $this->perPage = (int) $this->perPage;
        $this->anggotaPage = 1;
    }

    public function gotoAnggotaPage(int $page): void
    {
        $this->anggotaPage = max(1, $page);
    }

    /**
     * Buka modal detail siswa; hanya untuk anggota kelas ini.
     */
    public function bukaDetail(int $id): void
    {
        $this->detailSiswaId = in_array($id, $this->anggotaIds(), true) ? $id : null;
    }

    public function tutupDetail(): void
    {
        $this->detailSiswaId = null;
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
        $perPage = (int) $this->perPage;
        $isSemua = $perPage <= 0;

        if ($isSemua) {
            $anggota = $this->anggotaQuery()->get();
            $anggotaTotal = $anggota->count();
            $anggotaPager = [1];
            $nomorAwal = 0;
        } else {
            $anggota = $this->anggotaQuery()->paginate($perPage, ['*'], 'anggota_page', $this->anggotaPage);
            if ($this->anggotaPage > $anggota->lastPage()) {
                $this->anggotaPage = max(1, $anggota->lastPage());
                $anggota = $this->anggotaQuery()->paginate($perPage, ['*'], 'anggota_page', $this->anggotaPage);
            }
            $anggotaTotal = $anggota->total();
            $anggotaPager = $this->pagerPages($anggota->currentPage(), $anggota->lastPage());
            $nomorAwal = ($anggota->currentPage() - 1) * $perPage;
        }

        $detailSiswa = null;
        $detailTtl = '—';
        if ($this->detailSiswaId !== null) {
            $detailSiswa = Student::query()->whereIn('id', $this->anggotaIds())->with('guardians:id,nama')->find($this->detailSiswaId);
            if ($detailSiswa !== null) {
                $ttlParts = array_filter([
                    $detailSiswa->tempat_lahir,
                    $detailSiswa->tgl_lahir ? Carbon::parse($detailSiswa->tgl_lahir)->translatedFormat('d F Y') : null,
                ]);
                $detailTtl = $ttlParts !== [] ? implode(', ', $ttlParts) : '—';
            }
        }

        $pengurusRows = ClassOfficer::query()
            ->where('rombel_id', $this->rombel->id)
            ->where('academic_year_id', $this->rombel->academic_year_id)
            ->with('student:id,nama')
            ->get();

        $pengurus = [];
        foreach (ClassOfficer::JABATAN as $key => $label) {
            $pengurus[$key] = null;
        }
        foreach ($pengurusRows as $row) {
            $pengurus[$row->jabatan] = $row->student?->nama;
        }

        return view('livewire.akademik.rombel-detail', [
            'anggota' => $anggota,
            'anggotaTotal' => $anggotaTotal,
            'jumlahAnggota' => $this->anggotaIdsCount(),
            'anggotaPager' => $anggotaPager,
            'isSemua' => $isSemua,
            'nomorAwal' => $nomorAwal,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'detailSiswa' => $detailSiswa,
            'detailTtl' => $detailTtl,
            'pengurus' => $pengurus,
            'pengurusLabels' => ClassOfficer::JABATAN,
        ])->layout('layouts.app', ['title' => 'Kelas '.$this->rombel->nama]);
    }

    /** @var array<int>|null Cache ID anggota selama satu request. */
    private ?array $anggotaIdsCache = null;

    /**
     * ID anggota kelas aktif untuk tahun ajaran rombel.
     *
     * @return array<int>
     */
    private function anggotaIds(): array
    {
        if ($this->anggotaIdsCache === null) {
            $this->anggotaIdsCache = $this->rombel->students()
                ->wherePivot('academic_year_id', $this->rombel->academic_year_id)
                ->wherePivot('status', 'aktif')
                ->pluck('students.id')
                ->all();
        }

        return $this->anggotaIdsCache;
    }

    private function anggotaIdsCount(): int
    {
        return count($this->anggotaIds());
    }

    /**
     * Kueri anggota siap tampil (dengan pencarian dan sortir).
     */
    private function anggotaQuery(): Builder
    {
        $query = Student::query()->whereIn('id', $this->anggotaIds());

        if ($this->searchAnggota !== '') {
            $term = '%'.$this->searchAnggota.'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('nama', 'like', $term)
                    ->orWhere('nis', 'like', $term)
                    ->orWhere('nisn', 'like', $term);
            });
        }

        match ($this->sortAnggota) {
            'nama_desc' => $query->orderByDesc('nama'),
            'nis' => $query->orderBy('nis')->orderBy('nama'),
            'angkatan' => $query->orderByRaw('CASE WHEN angkatan IS NULL THEN 1 ELSE 0 END, angkatan')->orderBy('nama'),
            default => $query->orderBy('nama'),
        };

        return $query;
    }
}
