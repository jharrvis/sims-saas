<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int|null $school_id
 * @property Carbon|null $tgl_lahir
 * @property Carbon|null $tmt
 */
class Employee extends Model
{
    use BelongsToSchool, SoftDeletes;

    /**
     * Pendidikan terakhir (label tampilan).
     *
     * @var array<string,string>
     */
    public const PENDIDIKAN = [
        'sma' => 'SMA/SMK',
        'd3' => 'D3',
        'd4' => 'D4',
        's1' => 'S1',
        's2' => 'S2',
        's3' => 'S3',
    ];

    /**
     * Golongan/pangkat PNS.
     *
     * @var list<string>
     */
    public const GOLONGAN = [
        'I/a', 'I/b', 'I/c', 'I/d',
        'II/a', 'II/b', 'II/c', 'II/d',
        'III/a', 'III/b', 'III/c', 'III/d',
        'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e',
    ];

    /**
     * Status sertifikasi pendidik (label tampilan).
     *
     * @var array<string,string>
     */
    public const SERTIFIKASI = [
        'belum' => 'Belum Sertifikasi',
        'proses' => 'Proses Sertifikasi',
        'sudah' => 'Sudah Sertifikasi',
    ];

    protected $fillable = [
        'school_id',
        'import_batch_id',
        'nip_nuptk',
        'nik',
        'nama',
        'tempat_lahir',
        'tgl_lahir',
        'jk',
        'agama',
        'alamat',
        'email',
        'phone',
        'jenis',
        'status_kepegawaian',
        'jabatan',
        'pendidikan_terakhir',
        'jurusan',
        'perguruan_tinggi',
        'tahun_lulus',
        'tmt',
        'golongan',
        'sertifikasi',
        'no_sertifikat',
        'foto_path',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
            'tahun_lulus' => 'integer',
            'tmt' => 'date',
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Rombel, $this>
     */
    public function rombelsAsWali(): HasMany
    {
        return $this->hasMany(Rombel::class, 'wali_kelas_id');
    }

    /**
     * Jadwal mengajar guru ini. FK schedules.employee_id bersifat
     * restrictOnDelete — jadwal harus dipindah/dihapus sebelum data
     * pegawai boleh dihapus permanen.
     *
     * @return HasMany<Schedule, $this>
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'employee_id');
    }

    /**
     * Akun login yang tertaut ke data kepegawaian ini (maksimal satu,
     * lihat unique pada users.employee_id).
     *
     * @return HasOne<User, $this>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    public function labelJenis(): string
    {
        return match ($this->jenis) {
            'guru_mapel' => 'Guru Mapel',
            'guru_kelas' => 'Guru Kelas',
            default => 'Tendik',
        };
    }

    public function labelJk(): string
    {
        return match ($this->jk) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '—',
        };
    }

    public function labelPendidikan(): string
    {
        return self::PENDIDIKAN[$this->pendidikan_terakhir] ?? '—';
    }

    public function labelSertifikasi(): string
    {
        return self::SERTIFIKASI[$this->sertifikasi] ?? '—';
    }

    public function fotoUrl(): ?string
    {
        return $this->foto_path
            ? Storage::disk('public')->url($this->foto_path)
            : null;
    }
}
