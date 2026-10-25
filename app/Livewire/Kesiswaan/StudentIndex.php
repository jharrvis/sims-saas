<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Domains\Kesiswaan\Services\StudentPhotoService;
use App\Models\AcademicYear;
use App\Models\Rombel;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar siswa dengan pencarian + filter status/rombel (FR-104).
 * Kolom Kelas menampilkan rombel AKTIF siswa di tahun ajaran aktif.
 * Akses dibatasi permission siswa.view via route middleware.
 *
 * Fitur: bulk action (status/rombel/export/hapus massal) + recycle bin
 * (hapus = soft delete; pulihkan / hapus permanen dari tampilan sampah).
 */
class StudentIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $rombel = '';

    /** @var list<int> id siswa terpilih (checkbox) */
    public array $selected = [];

    public bool $selectAll = false;

    /** @var list<int> id siswa di halaman saat ini (untuk select-all) */
    public array $pageIds = [];

    public string $bulkAksi = '';

    public string $bulkStatus = '';

    public string $bulkRombelId = '';

    public bool $showTrashed = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatingRombel(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll(): void
    {
        $this->selected = $this->selectAll ? $this->pageIds : [];
    }

    public function updatedSelected(): void
    {
        $selected = array_map('intval', $this->selected);
        $this->selectAll = $this->pageIds !== [] && count(array_diff($this->pageIds, $selected)) === 0;
    }

    public function toggleTrashed(): void
    {
        $this->authorize('siswa.delete');

        $this->showTrashed = ! $this->showTrashed;
        $this->resetPage();
        $this->resetSelection();
        $this->bulkAksi = '';
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->rombel = '';
        $this->resetPage();
        $this->resetSelection();
    }

    public function delete(Student $student): void
    {
        $this->authorize('siswa.delete');
        $student->delete(); // Soft delete — bisa dipulihkan dari sampah.
        session()->flash('sukses', 'Data siswa dipindah ke sampah.');
    }

    public function restore(int $id): void
    {
        $this->authorize('siswa.delete');

        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();

        session()->flash('sukses', "Data {$student->nama} dipulihkan.");
    }

    public function forceDelete(int $id): void
    {
        $this->authorize('siswa.delete');

        $student = Student::onlyTrashed()->findOrFail($id);
        app(StudentPhotoService::class)->delete($student->foto_path);
        $student->forceDelete();

        session()->flash('sukses', "Data {$student->nama} dihapus permanen.");
    }

    public function bulkTerapkan(): void
    {
        if ($this->selected === []) {
            $this->addError('bulkAksi', 'Pilih minimal satu siswa.');

            return;
        }

        if ($this->showTrashed) {
            $this->bulkTerapkanTrashed();

            return;
        }

        match ($this->bulkAksi) {
            'status' => $this->bulkUbahStatus(),
            'rombel' => $this->bulkPindahRombel(),
            'export' => $this->bulkExport(),
            'hapus' => $this->bulkHapus(),
            default => $this->addError('bulkAksi', 'Pilih aksi massal dulu.'),
        };
    }

    private function bulkUbahStatus(): void
    {
        $this->authorize('siswa.update');

        $this->validate([
            'bulkStatus' => ['required', Rule::in(['aktif', 'pindah', 'lulus', 'keluar', 'do'])],
        ]);

        Student::whereIn('id', $this->selected)->update(['status' => $this->bulkStatus]);
        session()->flash('sukses', count($this->selected).' siswa diubah statusnya menjadi '.ucfirst($this->bulkStatus).'.');
        $this->resetSelection();
        $this->bulkAksi = '';
    }

    private function bulkPindahRombel(): void
    {
        $this->authorize('siswa.update');

        $this->validate([
            'bulkRombelId' => ['required', 'integer', 'exists:rombels,id'],
        ]);

        $tahunAktifId = AcademicYear::where('aktif', true)->value('id');

        if (! $tahunAktifId) {
            $this->addError('bulkAksi', 'Tidak ada tahun ajaran aktif.');

            return;
        }

        if (! AcademicYear::masihTerbuka($tahunAktifId)) {
            $this->addError('bulkAksi', 'Tahun ajaran aktif sudah ditutup — data bersifat arsip read-only.');

            return;
        }

        $rombelId = (int) $this->bulkRombelId;
        $rombel = Rombel::findOrFail($rombelId);
        $count = 0;

        DB::transaction(function () use ($tahunAktifId, $rombelId, &$count): void {
            $students = Student::whereIn('id', $this->selected)->get();

            foreach ($students as $student) {
                // Lewati bila siswa sudah berada di rombel tujuan.
                $sudahDiTujuan = DB::table('class_student')
                    ->where('student_id', $student->id)
                    ->where('rombel_id', $rombelId)
                    ->where('academic_year_id', $tahunAktifId)
                    ->where('status', 'aktif')
                    ->exists();

                if ($sudahDiTujuan) {
                    continue;
                }

                // Tutup rombel aktif lama (jadi riwayat pindah).
                DB::table('class_student')
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $tahunAktifId)
                    ->where('status', 'aktif')
                    ->update(['status' => 'pindah']);

                $student->rombels()->attach($rombelId, [
                    'academic_year_id' => $tahunAktifId,
                    'status' => 'aktif',
                ]);
                $count++;
            }
        });

        session()->flash('sukses', "{$count} siswa dipindah ke rombel {$rombel->nama}.");
        $this->resetSelection();
        $this->bulkAksi = '';
    }

    private function bulkExport(): void
    {
        $this->authorize('siswa.view');

        $ids = implode('&ids[]=', array_map('intval', $this->selected));
        $this->redirect(route('siswa.ekspor.excel').'?ids[]='.$ids, navigate: false);
    }

    private function bulkHapus(): void
    {
        $this->authorize('siswa.delete');

        Student::whereIn('id', $this->selected)->delete(); // Soft delete.
        session()->flash('sukses', count($this->selected).' siswa dipindah ke sampah.');
        $this->resetSelection();
        $this->bulkAksi = '';
    }

    private function bulkTerapkanTrashed(): void
    {
        $this->authorize('siswa.delete');

        match ($this->bulkAksi) {
            'pulihkan' => $this->bulkPulihkan(),
            'hapus_permanen' => $this->bulkHapusPermanen(),
            default => $this->addError('bulkAksi', 'Pilih aksi massal dulu.'),
        };
    }

    private function bulkPulihkan(): void
    {
        Student::onlyTrashed()->whereIn('id', $this->selected)->restore();
        session()->flash('sukses', count($this->selected).' siswa dipulihkan dari sampah.');
        $this->resetSelection();
        $this->bulkAksi = '';
    }

    private function bulkHapusPermanen(): void
    {
        $students = Student::onlyTrashed()->whereIn('id', $this->selected)->get();
        $photoService = app(StudentPhotoService::class);

        foreach ($students as $student) {
            $photoService->delete($student->foto_path);
            $student->forceDelete();
        }

        session()->flash('sukses', count($this->selected).' siswa dihapus permanen.');
        $this->resetSelection();
        $this->bulkAksi = '';
    }

    private function resetSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function render(): View
    {
        $tahunAktifId = AcademicYear::where('aktif', true)->value('id');

        $students = Student::query()
            ->when($this->showTrashed, fn ($q) => $q->onlyTrashed())
            // Rombel aktif di tahun ajaran aktif (satu siswa = satu rombel aktif per tahun).
            ->with(['rombels' => function ($q) use ($tahunAktifId): void {
                $q->wherePivot('status', 'aktif');
                if ($tahunAktifId) {
                    $q->wherePivot('academic_year_id', $tahunAktifId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }])
            ->when($this->search !== '', fn ($q) => $q->where(function ($w): void {
                $w->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nis', 'like', '%'.$this->search.'%')
                    ->orWhere('nisn', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->rombel !== '', function ($q) use ($tahunAktifId): void {
                // wherePivot() tidak boleh di dalam whereHas (menghasilkan
                // SQL salah `pivot = status`); pakai kolom pivot qualified.
                $q->whereHas('rombels', function ($w) use ($tahunAktifId): void {
                    $w->where('class_student.status', 'aktif');
                    if ($tahunAktifId) {
                        $w->where('class_student.academic_year_id', $tahunAktifId);
                    }
                    $w->where('rombels.id', (int) $this->rombel);
                });
            })
            ->orderBy('nama')
            ->paginate(15);

        $this->pageIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();
        // Jaga agar pilihan lama yang tidak lagi tampil tidak ikut terkirim.
        $this->selected = array_values(array_intersect(
            array_map('intval', $this->selected),
            $this->pageIds,
        ));

        $rombels = $tahunAktifId
            ? Rombel::where('academic_year_id', $tahunAktifId)->orderBy('tingkat')->orderBy('nama')->get()
            : collect();

        $statusCounts = Student::query()
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('livewire.kesiswaan.student-index', [
            'students' => $students,
            'rombels' => $rombels,
            'trashedCount' => Student::onlyTrashed()->count(),
            'statusCounts' => $statusCounts,
        ])->layout('layouts.app', ['title' => 'Data Siswa']);
    }
}
