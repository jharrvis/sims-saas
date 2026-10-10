<?php

declare(strict_types=1);

namespace App\Livewire\Survei;

use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Form buat/ubah survei + pertanyaan.
 */
class SurveyForm extends Component
{
    public ?Survey $survey = null;

    public string $judul = '';

    public string $deskripsi = '';

    public string $mulai = '';

    public string $selesai = '';

    public string $status = 'draf';

    public string $target_type = 'semua';

    /** @var array<int, array{pertanyaan: string, tipe: string, opsi: string, wajib: bool}> */
    public array $questions = [];

    public function mount(?Survey $survey = null): void
    {
        $this->authorize($survey?->exists ? 'survei.update' : 'survei.create');

        if ($survey?->exists) {
            $this->survey = $survey;
            $this->judul = $survey->judul;
            $this->deskripsi = $survey->deskripsi ?? '';
            /** @var Carbon|null $mulai */
            $mulai = $survey->mulai;
            /** @var Carbon|null $selesai */
            $selesai = $survey->selesai;
            $this->mulai = $mulai?->format('Y-m-d\TH:i') ?? '';
            $this->selesai = $selesai?->format('Y-m-d\TH:i') ?? '';
            $this->status = $survey->status;
            $this->target_type = $survey->target_type;
            $this->questions = $survey->questions->map(function ($q): array {
                /** @var array<int,string>|null $opsi */
                $opsi = $q->opsi;

                return [
                    'pertanyaan' => $q->pertanyaan,
                    'tipe' => $q->tipe,
                    'opsi' => is_array($opsi) ? implode("\n", $opsi) : '',
                    'wajib' => (bool) $q->wajib,
                ];
            })->all();
        }
    }

    public function tambahPertanyaan(): void
    {
        $this->questions[] = ['pertanyaan' => '', 'tipe' => 'pilihan_ganda', 'opsi' => '', 'wajib' => true];
    }

    public function hapusPertanyaan(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function save(): void
    {
        $data = $this->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'mulai' => ['nullable', 'date'],
            'selesai' => ['nullable', 'date', 'after_or_equal:mulai'],
            'status' => ['required', Rule::in(['draf', 'aktif', 'tutup'])],
            'target_type' => ['required', Rule::in(['semua', 'siswa', 'guru', 'orang_tua'])],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.pertanyaan' => ['required', 'string', 'max:2000'],
            'questions.*.tipe' => ['required', Rule::in(['pilihan_ganda', 'skala', 'teks'])],
            'questions.*.opsi' => ['nullable', 'string'],
            'questions.*.wajib' => ['boolean'],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = [
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?: null,
            'mulai' => $data['mulai'] ?: null,
            'selesai' => $data['selesai'] ?: null,
            'status' => $data['status'],
            'target_type' => $data['target_type'],
            'dibuat_oleh' => auth()->id(),
        ];

        $survey = $this->survey?->exists ? tap($this->survey)->update($payload) : Survey::create($payload);

        $survey->questions()->delete();

        foreach ($data['questions'] as $i => $q) {
            $opsi = $q['tipe'] === 'pilihan_ganda'
                ? array_values(array_filter(array_map('trim', explode("\n", (string) ($q['opsi'] ?? '')))))
                : null;

            $survey->questions()->create([
                'pertanyaan' => $q['pertanyaan'],
                'tipe' => $q['tipe'],
                'opsi' => $opsi,
                'urutan' => $i + 1,
                'wajib' => (bool) $q['wajib'],
            ]);
        }

        session()->flash('ok', 'Survei disimpan.');
        $this->redirectRoute('survei.index');
    }

    public function render(): View
    {
        return view('livewire.survei.survey-form', [
            'isEdit' => (bool) $this->survey?->exists,
        ])->layout('layouts.app', ['title' => $this->survey?->exists ? 'Ubah Survei' : 'Buat Survei']);
    }
}
