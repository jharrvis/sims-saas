<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Guard yang didukung model ini: `web` (tenant) + `platform`
     * (operator SaaS). Cara resmi Spatie agar satu model User dapat
     * memegang peran/permission guard `platform` (Dok 05 §4.3)
     * tanpa mencampuri permission tenant guard `web`.
     *
     * @return array<int, string>
     */
    public function guardName(): array
    {
        return ['web', 'platform'];
    }

    protected $fillable = [
        'username',
        'name',
        'email',
        'phone',
        'password',
        'is_platform',
        'student_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform' => 'boolean',
        ];
    }

    /**
     * Keanggotaan user pada tenant (ADR-0004: identitas global + membership).
     *
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function activeMemberships(): HasMany
    {
        return $this->memberships()->aktif();
    }

    /**
     * Nama route tujuan setelah login berhasil.
     * Akun peran ganda (operator platform + admin sekolah) memilih dulu
     * di halaman pilih-peran.
     */
    public function ruteSetelahLogin(): string
    {
        if ($this->is_platform && $this->activeMemberships()->exists()) {
            return 'pilih-peran';
        }

        return $this->is_platform ? 'platform.dashboard' : 'dashboard';
    }

    /**
     * @return HasOne<MfaSecret, $this>
     */
    public function mfaSecret(): HasOne
    {
        return $this->hasOne(MfaSecret::class);
    }

    /**
     * Apakah 2FA (TOTP) sudah diaktifkan & dikonfirmasi user ini.
     * 2FA bersifat opt-in: default nonaktif untuk semua pengguna.
     */
    public function hasMfaEnabled(): bool
    {
        return (bool) $this->mfaSecret?->isConfirmed();
    }

    /**
     * Apakah user ini WAJIB memakai 2FA.
     *
     * - Selalu true bila user sudah mengaktifkan 2FA sendiri (opt-in).
     * - Operator platform hanya wajib bila config mfa.require_for_platform=true.
     */
    public function mfaRequired(): bool
    {
        return $this->hasMfaEnabled()
            || ($this->is_platform && (bool) config('mfa.require_for_platform', false));
    }

    /**
     * Tautan akun siswa 1:1 (login PWA siswa).
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Tautan akun orang tua ke data wali (multi-anak lintas sekolah).
     *
     * @return BelongsToMany<Guardian, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_user')->withTimestamps();
    }
}
