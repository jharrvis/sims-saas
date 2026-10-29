<?php

declare(strict_types=1);

namespace App\Livewire\Kepegawaian;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\User;
use App\Support\RoleCatalog;
use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

/**
 * Buatkan akun login untuk satu guru/pegawai.
 *
 * Menutup celah "daftar guru tidak masuk daftar pengguna": akun dibuat dari
 * data kepegawaian (nama + kontak diambil dari Employee) dan ditautkan lewat
 * users.employee_id, sehingga daftar Pengguna bisa menampilkan guru/pegawai
 * pemilik akun. Satu Employee maksimal punya satu User (unique di kolom).
 *
 * Pola pembuatan akun mengikuti PenggunaForm: identitas global (ADR-0004),
 * membership pada sekolah aktif, peran pada tim sekolah tersebut.
 */
class EmployeeAccountForm extends Component
{
    #[Locked]
    public Employee $employee;

    public string $username = '';

    public string $phone = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** @var array<int,string> */
    public array $roles = [];

    public ?string $error = null;

    /**
     * Peran bawaan yang disarankan per jenis kepegawaian. Admin tetap bisa
     * mengubah pilihan peran di form; nilai ini hanya default awal.
     *
     * @var array<string,string>
     */
    public const PERAN_DEFAULT = [
        'guru_mapel' => 'guru-mapel',
        'guru_kelas' => 'wali-kelas',
        'tendik' => 'tata-usaha',
    ];

    public function mount(Employee $employee): void
    {
        abort_unless(auth()->user()?->can('pengguna.create'), 403);

        $this->employee = $employee;

        if ($employee->user()->exists()) {
            session()->flash('sukses', 'Guru/pegawai ini sudah memiliki akun login.');

            $this->redirectRoute('guru.show', $employee);

            return;
        }

        $this->phone = $this->normalisasiTelepon((string) ($employee->phone ?? ''));
        $this->email = (string) ($employee->email ?? '');

        $peranDefault = self::PERAN_DEFAULT[$employee->jenis] ?? null;
        $peranTersedia = Role::query()
            ->where('team_id', $this->schoolId())
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        if ($peranDefault !== null && in_array($peranDefault, $peranTersedia, true)) {
            $this->roles = [$peranDefault];
        }
    }

    public function simpan(): void
    {
        abort_unless(auth()->user()?->can('pengguna.create'), 403);

        $schoolId = $this->schoolId();
        $this->phone = $this->normalisasiTelepon($this->phone);
        $this->error = null;

        $this->validate();

        if ($this->phone === '' && $this->password === '') {
            $this->addError('phone', 'Isi nomor WhatsApp atau kata sandi agar pengguna dapat masuk.');

            return;
        }

        if ($this->employee->user()->exists()) {
            $this->addError('username', 'Guru/pegawai ini sudah memiliki akun login.');

            return;
        }

        try {
            $this->simpanAkun($schoolId);
        } catch (QueryException $e) {
            // Dua admin menekan simpan hampir bersamaan: unique
            // users.employee_id menolak yang kedua — ubah menjadi pesan
            // form yang ramah, bukan 500 (QA B-16).
            $pesan = $e->getMessage();
            if (str_contains($pesan, 'users_employee_id_unique') || str_contains($pesan, 'users.employee_id')) {
                $this->addError('username', 'Guru/pegawai ini baru saja dibuatkan akun oleh proses lain. Muat ulang halaman untuk melihat akunnya.');

                return;
            }

            throw $e;
        }

        session()->flash('sukses', 'Akun login berhasil dibuat dan ditautkan ke data guru/pegawai.');

        $this->redirectRoute('guru.show', $this->employee);
    }

    private function simpanAkun(int $schoolId): void
    {
        DB::transaction(function () use ($schoolId): void {
            /** @var User $user */
            $user = User::create([
                'name' => $this->employee->nama,
                'username' => $this->username,
                'phone' => $this->phone !== '' ? $this->phone : null,
                'email' => $this->email !== '' ? $this->email : null,
                'password' => $this->password !== '' ? $this->password : null,
                'is_platform' => false,
                'employee_id' => $this->employee->id,
            ]);

            Membership::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'status' => $this->employee->status_aktif ? 'aktif' : 'dinonaktifkan',
            ]);

            $user->assignRole($this->roles);

            AuditLog::create([
                'school_id' => $schoolId,
                'user_id' => auth()->id(),
                'aksi' => 'pengguna.create',
                'model_type' => User::class,
                'model_id' => (string) $user->id,
                'before' => null,
                'after' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'phone' => $user->phone,
                    'status' => $this->employee->status_aktif ? 'aktif' : 'dinonaktifkan',
                    'roles' => $this->roles,
                    'employee_id' => $this->employee->id,
                    'employee_nama' => $this->employee->nama,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);
        });
    }

    public function render(): View
    {
        $roles = Role::query()
            ->where('team_id', $this->schoolId())
            ->where('guard_name', 'web')
            ->get()
            ->sortBy(fn (Role $role) => RoleCatalog::sortIndex($role->name).'-'.$role->name)
            ->values();

        return view('livewire.kepegawaian.employee-account-form', [
            'rolesTersedia' => $roles,
        ])->layout('layouts.app', ['title' => 'Buatkan Akun Login']);
    }

    /**
     * @return array<string,mixed>
     */
    protected function rules(): array
    {
        $schoolId = $this->schoolId();

        return [
            'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^62[0-9]{8,15}$/', Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')
                    ->where('team_id', $schoolId)
                    ->where('guard_name', 'web'),
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    protected function messages(): array
    {
        return [
            'phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.',
            'roles.required' => 'Pilih minimal satu peran untuk akun ini.',
            'roles.min' => 'Pilih minimal satu peran untuk akun ini.',
        ];
    }

    private function schoolId(): int
    {
        $schoolId = (int) app(TenantContext::class)->schoolId();
        abort_if($schoolId === 0, 403);

        return $schoolId;
    }

    private function normalisasiTelepon(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
