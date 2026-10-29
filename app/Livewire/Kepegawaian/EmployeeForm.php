<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Domains\Kepegawaian\Services\EmployeeAccountSync;
use App\Domains\Kesiswaan\Services\StudentPhotoService;
use App\Models\Employee;
use App\Models\Student;
use App\Tenancy\TenantContext;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Form tambah/ubah guru & pegawai (FR-301).
 *
 * Paket data: A (identitas & kontak), B (pendidikan), C (kepegawaian).
 */
class EmployeeForm extends Component
{
    use WithFileUploads;

    public ?Employee $employee = null;

    public string $nip_nuptk = '';

    // A — identitas & kontak
    public string $nik = '';

    public string $nama = '';

    public string $tempat_lahir = '';

    public string $tgl_lahir = '';

    public string $jk = '';

    public string $agama = '';

    public string $alamat = '';

    public string $email = '';

    public string $phone = '';

    /** @var mixed */
    public $foto = null;

    public bool $hapusFoto = false;

    public string $jenis = 'guru_mapel';

    public string $status_kepegawaian = 'honorer';

    public string $jabatan = '';

    // B — pendidikan
    public string $pendidikan_terakhir = '';

    public string $jurusan = '';

    public string $perguruan_tinggi = '';

    public string $tahun_lulus = '';

    // C — kepegawaian
    public string $tmt = '';

    public string $golongan = '';

    public string $sertifikasi = '';

    public string $no_sertifikat = '';

    public bool $status_aktif = true;

    public function mount(?Employee $employee = null): void
    {
        if ($employee?->exists) {
            $this->employee = $employee;
            $this->nip_nuptk = $employee->nip_nuptk ?? '';
            $this->nik = $employee->nik ?? '';
            $this->nama = $employee->nama;
            $this->tempat_lahir = $employee->tempat_lahir ?? '';
            $this->tgl_lahir = $employee->tgl_lahir?->format('Y-m-d') ?? '';
            $this->jk = $employee->jk ?? '';
            $this->agama = $employee->agama ?? '';
            $this->alamat = $employee->alamat ?? '';
            $this->email = $employee->email ?? '';
            $this->phone = $employee->phone ?? '';
            $this->jenis = $employee->jenis;
            $this->status_kepegawaian = $employee->status_kepegawaian;
            $this->jabatan = $employee->jabatan ?? '';
            $this->pendidikan_terakhir = $employee->pendidikan_terakhir ?? '';
            $this->jurusan = $employee->jurusan ?? '';
            $this->perguruan_tinggi = $employee->perguruan_tinggi ?? '';
            $this->tahun_lulus = $employee->tahun_lulus !== null ? (string) $employee->tahun_lulus : '';
            $this->tmt = $employee->tmt?->format('Y-m-d') ?? '';
            $this->golongan = $employee->golongan ?? '';
            $this->sertifikasi = $employee->sertifikasi ?? '';
            $this->no_sertifikat = $employee->no_sertifikat ?? '';
            $this->status_aktif = $employee->status_aktif;
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        return [
            // Identitas pegawai unik per sekolah, selaras indeks unik
            // database (QA B-10); NIK disamakan 16 digit seperti wali (B-19).
            'nip_nuptk' => ['nullable', 'string', 'max:30', Rule::unique('employees')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->employee?->id)],
            'nik' => ['nullable', 'string', 'digits:16', Rule::unique('employees')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->employee?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['nullable', 'date'],
            'jk' => ['nullable', Rule::in(['L', 'P'])],
            'agama' => ['nullable', Rule::in(Student::AGAMAS)],
            'alamat' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('employees')->where(
                fn ($q) => $q->where('school_id', $schoolId)
            )->ignore($this->employee?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'foto' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
            'jenis' => ['required', Rule::in(['guru_mapel', 'guru_kelas', 'tendik'])],
            'status_kepegawaian' => ['required', Rule::in(['pns', 'pppk', 'honorer', 'kontrak'])],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'pendidikan_terakhir' => ['nullable', Rule::in(array_keys(Employee::PENDIDIKAN))],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'perguruan_tinggi' => ['nullable', 'string', 'max:150'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1950', 'max:'.date('Y')],
            'tmt' => ['nullable', 'date'],
            'golongan' => ['nullable', Rule::in(Employee::GOLONGAN)],
            'sertifikasi' => ['nullable', Rule::in(array_keys(Employee::SERTIFIKASI))],
            'no_sertifikat' => ['nullable', 'string', 'max:50'],
            'status_aktif' => ['boolean'],
        ];
    }

    public function save(): void
    {
        // Authorization berjalan sebelum validasi dan sebelum ada upload foto:
        // route middleware hanya melindungi GET, bukan aksi Livewire.
        $this->authorize($this->employee?->exists ? 'guru.update' : 'guru.create');

        $this->validate();

        $data = [
            'nip_nuptk' => $this->nip_nuptk ?: null,
            'nik' => $this->nik ?: null,
            'nama' => $this->nama,
            'tempat_lahir' => $this->tempat_lahir ?: null,
            'tgl_lahir' => $this->tgl_lahir ?: null,
            'jk' => $this->jk ?: null,
            'agama' => $this->agama ?: null,
            'alamat' => $this->alamat ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'jenis' => $this->jenis,
            'status_kepegawaian' => $this->status_kepegawaian,
            'jabatan' => $this->jabatan ?: null,
            'pendidikan_terakhir' => $this->pendidikan_terakhir ?: null,
            'jurusan' => $this->jurusan ?: null,
            'perguruan_tinggi' => $this->perguruan_tinggi ?: null,
            'tahun_lulus' => $this->tahun_lulus !== '' ? (int) $this->tahun_lulus : null,
            'tmt' => $this->tmt ?: null,
            'golongan' => $this->golongan ?: null,
            'sertifikasi' => $this->sertifikasi ?: null,
            'no_sertifikat' => $this->no_sertifikat ?: null,
            'status_aktif' => $this->status_aktif,
        ];

        $photoService = app(StudentPhotoService::class);
        $schoolId = app(TenantContext::class)->requireSchoolId();
        $oldFotoPath = $this->employee?->foto_path;

        // Kompresi + simpan foto baru dulu; file lama dihapus setelah data tersimpan.
        $newFotoPath = $this->foto
            ? $photoService->store($this->foto, $schoolId, 'guru-foto')
            : null;

        if ($newFotoPath) {
            $data['foto_path'] = $newFotoPath;
        } elseif ($this->hapusFoto && $this->employee?->exists) {
            $data['foto_path'] = null;
        }

        if ($this->employee?->exists) {
            $this->employee->update($data);
            // Status akun login tertaut mengikuti status_aktif (QA B-01).
            app(EmployeeAccountSync::class)->syncMembershipStatus($this->employee);
        } else {
            Employee::create($data);
        }

        if ($newFotoPath || ($this->hapusFoto && $this->employee?->exists)) {
            $photoService->delete($oldFotoPath);
        }

        $this->redirect(route('guru.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.kepegawaian.employee-form')
            ->layout('layouts.app', ['title' => $this->employee?->exists ? 'Ubah Guru' : 'Tambah Guru']);
    }
}
