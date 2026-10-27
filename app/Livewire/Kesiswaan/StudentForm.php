<?php

declare(strict_types=1);

namespace App\Livewire\Kesiswaan;

use App\Domains\Kesiswaan\Services\StudentPhotoService;
use App\Domains\Platform\Services\QuotaService;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Form tambah/ubah siswa (FR-101).
 */
class StudentForm extends Component
{
    use WithFileUploads;

    public ?Student $student = null;

    public string $nis = '';

    public string $nisn = '';

    public string $angkatan = '';

    public string $nama = '';

    public string $jk = 'L';

    public string $tempat_lahir = '';

    public string $tgl_lahir = '';

    public string $agama = '';

    public string $alamat = '';

    public string $phone = '';

    /** @var mixed File upload Livewire (TemporaryUploadedFile) */
    public $foto = null;

    public bool $hapusFoto = false;

    public function mount(?Student $student = null): void
    {
        if ($student?->exists) {
            $this->student = $student;
            $this->nis = $student->nis;
            $this->nisn = $student->nisn ?? '';
            $this->angkatan = $student->angkatan !== null ? (string) $student->angkatan : '';
            $this->nama = $student->nama;
            $this->jk = $student->jk;
            $this->tempat_lahir = $student->tempat_lahir ?? '';
            $this->tgl_lahir = $student->tgl_lahir?->format('Y-m-d') ?? '';
            $this->agama = $student->agama ?? '';
            $this->alamat = $student->alamat ?? '';
            $this->phone = $student->phone ?? '';
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        return [
            'nis' => ['required', 'string', 'max:30', Rule::unique('students')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->student?->id)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->student?->id)],
            'angkatan' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'nama' => ['required', 'string', 'max:150'],
            'jk' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', Rule::in(Student::AGAMAS)],
            'alamat' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            // Foto: maks 2 MB file asli; dikompresi ke maks 512×512 JPEG saat simpan.
            'foto' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->student?->exists ? 'siswa.update' : 'siswa.create');

        $this->validate();

        // US-026: arsip read-only — tolak bila tahun ajaran aktif sudah ditutup.
        $taId = AcademicYear::where('aktif', true)->value('id');
        if ($taId === null) {
            $this->addError('nama', 'Tidak ada tahun ajaran aktif.');

            return;
        }
        if (! AcademicYear::masihTerbuka($taId)) {
            $this->addError('nama', 'Tahun ajaran aktif sudah ditutup — data siswa bersifat arsip read-only.');

            return;
        }

        // Kuota siswa (trial: maks. 100) — hanya untuk siswa baru.
        if (! $this->student?->exists) {
            $quota = app(QuotaService::class)->check('siswa_aktif');

            if (! $quota['boleh']) {
                $this->addError('nama', 'Kuota siswa tercapai ('.$quota['terpakai'].'/'.$quota['batas'].'). Hubungi operator untuk upgrade paket.');

                return;
            }
        }

        $data = [
            'nis' => $this->nis,
            'nisn' => $this->nisn ?: null,
            'angkatan' => $this->angkatan !== '' ? (int) $this->angkatan : null,
            'nama' => $this->nama,
            'jk' => $this->jk,
            'tempat_lahir' => $this->tempat_lahir ?: null,
            'tgl_lahir' => $this->tgl_lahir ?: null,
            'agama' => $this->agama ?: null,
            'alamat' => $this->alamat ?: null,
            'phone' => $this->phone ?: null,
        ];

        $photoService = app(StudentPhotoService::class);
        $schoolId = app(TenantContext::class)->requireSchoolId();
        $oldFotoPath = $this->student?->foto_path;

        // Kompresi + simpan foto baru dulu; file lama dihapus setelah data tersimpan.
        $newFotoPath = $this->foto
            ? $photoService->store($this->foto, $schoolId)
            : null;

        if ($newFotoPath) {
            $data['foto_path'] = $newFotoPath;
        } elseif ($this->hapusFoto && $this->student?->exists) {
            $data['foto_path'] = null;
        }

        if ($this->student?->exists) {
            $this->student->update($data);
        } else {
            Student::create($data);
            // US-112 (webhook keluar siswa_baru) adalah story Fase 2 yang
            // belum dibangun — tidak ada WebhookService di codebase, dan
            // pemanggilan sebelumnya membuat setiap tambah siswa fatal.
            // Panggilan dihapus sampai fitur webhook Fase 2 diimplementasi.
        }

        if ($newFotoPath || ($this->hapusFoto && $this->student?->exists)) {
            $photoService->delete($oldFotoPath);
        }

        $this->redirect(route('siswa.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.kesiswaan.student-form')
            ->layout('layouts.app', ['title' => $this->student?->exists ? 'Ubah Siswa' : 'Tambah Siswa']);
    }
}
