<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pertanyaan survei.
 */
class SurveyQuestion extends Model
{
    use BelongsToSchool;

    public const TIPE = [
        'pilihan_ganda' => 'Pilihan Ganda',
        'skala' => 'Skala (1–5)',
        'teks' => 'Isian Singkat',
    ];

    protected $fillable = [
        'survey_id',
        'pertanyaan',
        'tipe',
        'opsi',
        'urutan',
        'wajib',
    ];

    protected function casts(): array
    {
        return [
            'opsi' => 'array',
            'wajib' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * @return HasMany<SurveyAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Agregat jawaban: hitung per opsi (pilihan_ganda) / rata-rata (skala).
     *
     * @return array<string,mixed>
     */
    public function agregat(): array
    {
        $jawaban = $this->answers()->pluck('jawaban');

        if ($this->tipe === 'skala') {
            $angka = $jawaban->map(fn (string $j): float => (float) $j)->filter(fn (float $v): bool => $v > 0);

            return [
                'responden' => $jawaban->count(),
                'rata_rata' => $angka->isNotEmpty() ? round($angka->avg(), 2) : null,
            ];
        }

        if ($this->tipe === 'pilihan_ganda') {
            return [
                'responden' => $jawaban->count(),
                'distribusi' => $jawaban->countBy()->sortDesc()->all(),
            ];
        }

        return ['responden' => $jawaban->count()];
    }
}
