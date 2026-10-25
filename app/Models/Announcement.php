<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengumuman tersegmentasi (FR-1103): target semua / peran / kelas,
 * dengan periode tayang dan status draf|tayang.
 */
class Announcement extends Model
{
    use BelongsToSchool;

    public const TARGET_TYPES = [
        'semua' => 'Semua Pengguna',
        'peran' => 'Peran Tertentu',
        'kelas' => 'Kelas Tertentu',
    ];

    public const STATUS = [
        'draf' => 'Draf',
        'tayang' => 'Tayang',
    ];

    protected $fillable = [
        'judul',
        'isi',
        'target_type',
        'target_data',
        'penting',
        'status',
        'tayang_dari',
        'tayang_sampai',
        'dibuat_oleh',
    ];

    protected $attributes = [
        'target_type' => 'semua',
        'status' => 'draf',
        'penting' => false,
    ];

    protected function casts(): array
    {
        return [
            'target_data' => 'array',
            'penting' => 'boolean',
            'tayang_dari' => 'date',
            'tayang_sampai' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Scope pengumuman yang sedang tayang pada tanggal tertentu.
     *
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    public function scopeTayang(Builder $query, ?string $tanggal = null): Builder
    {
        $tanggal ??= now()->toDateString();

        return $query->where('status', 'tayang')
            ->whereDate('tayang_dari', '<=', $tanggal)
            ->where(fn (Builder $q): Builder => $q->whereNull('tayang_sampai')->orWhereDate('tayang_sampai', '>=', $tanggal));
    }

    /**
     * Batasi pengumuman PWA ke audiens peran dan kelas siswa aktif.
     *
     * @param  array<int>  $rombelIds
     * @return Builder<Announcement>
     */
    public function scopeVisibleToPwa(Builder $query, ?string $role, array $rombelIds = []): Builder
    {
        $rombelIds = array_values(array_unique(array_map('intval', $rombelIds)));

        return $query->where(function (Builder $audience) use ($role, $rombelIds): void {
            $audience->where('target_type', 'semua');

            if ($role !== null) {
                $audience->orWhere(function (Builder $roles) use ($role): void {
                    $roles->where('target_type', 'peran')
                        ->whereJsonContains('target_data->roles', $role);
                });
            }

            if ($rombelIds !== []) {
                $audience->orWhere(function (Builder $classes) use ($rombelIds): void {
                    $classes->where('target_type', 'kelas')
                        ->where(function (Builder $targets) use ($rombelIds): void {
                            foreach ($rombelIds as $rombelId) {
                                $targets->orWhereJsonContains('target_data->rombel_ids', $rombelId);
                            }
                        });
                });
            }
        });
    }

    public function getTargetLabelAttribute(): string
    {
        /** @var array<string,mixed>|null $target */
        $target = $this->target_data;

        $base = self::TARGET_TYPES[$this->target_type];

        if ($this->target_type === 'peran') {
            return $base.': '.implode(', ', $target['roles'] ?? []);
        }

        if ($this->target_type === 'kelas') {
            return $base.' ('.count($target['rombel_ids'] ?? []).' rombel)';
        }

        return $base;
    }
}
