<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Broadcast notifikasi terjadwal (WA / notifikasi in-app).
 */
class Broadcast extends Model
{
    use BelongsToSchool;

    public const CHANNEL = [
        'whatsapp' => 'WhatsApp',
        'notifikasi' => 'Notifikasi In-App',
    ];

    public const TARGET = [
        'semua' => 'Semua',
        'siswa' => 'Siswa',
        'guru' => 'Guru/Karyawan',
        'rombel' => 'Per Kelas',
    ];

    public const STATUS = [
        'draf' => 'Draf',
        'terjadwal' => 'Terjadwal',
        'terkirim' => 'Terkirim',
        'gagal' => 'Gagal',
    ];

    protected $fillable = [
        'judul',
        'pesan',
        'channel',
        'target_type',
        'target_data',
        'dijadwalkan_pada',
        'status',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'target_data' => 'array',
            'dijadwalkan_pada' => 'datetime',
        ];
    }

    /**
     * @return HasMany<BroadcastLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(BroadcastLog::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function isSiapKirim(): bool
    {
        /** @var Carbon|null $jadwal */
        $jadwal = $this->dijadwalkan_pada;

        return in_array($this->status, ['draf', 'terjadwal'], true)
            && ($jadwal === null || $jadwal->lte(now()));
    }
}
