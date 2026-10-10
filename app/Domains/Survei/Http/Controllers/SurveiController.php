<?php

declare(strict_types=1);

namespace App\Domains\Survei\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API Survei & Evaluasi v1 (modul add-on — di belakang middleware module:survei).
 */
class SurveiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Survey::withCount(['questions', 'responses'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->orderByDesc('created_at')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'mulai' => ['nullable', 'date'],
            'selesai' => ['nullable', 'date', 'after_or_equal:mulai'],
            'status' => ['sometimes', Rule::in(['draf', 'aktif', 'tutup'])],
            'target_type' => ['sometimes', Rule::in(['semua', 'siswa', 'guru', 'orang_tua'])],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.pertanyaan' => ['required', 'string', 'max:2000'],
            'questions.*.tipe' => ['required', Rule::in(['pilihan_ganda', 'skala', 'teks'])],
            'questions.*.opsi' => ['nullable', 'array'],
            'questions.*.wajib' => ['sometimes', 'boolean'],
        ]);

        /** @var array<string,mixed> $payload */
        $payload = $data;
        $questions = $payload['questions'];
        unset($payload['questions']);
        $payload['dibuat_oleh'] = $request->user()->id;

        $survey = Survey::create($payload);

        foreach ($questions as $i => $q) {
            /** @var array<string,mixed> $q */
            $survey->questions()->create([
                'pertanyaan' => $q['pertanyaan'],
                'tipe' => $q['tipe'],
                'opsi' => $q['opsi'] ?? null,
                'urutan' => $i + 1,
                'wajib' => (bool) ($q['wajib'] ?? true),
            ]);
        }

        return response()->json($survey->load('questions'), 201);
    }

    public function show(Survey $survey): JsonResponse
    {
        return response()->json($survey->load('questions'));
    }

    public function submit(Request $request, Survey $survey): JsonResponse
    {
        abort_if(! $survey->isDapatDiisi(), 422, 'Survei tidak sedang dibuka.');

        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*.question_id' => ['required', 'integer', 'exists:survey_questions,id'],
            'jawaban.*.isi' => ['nullable', 'string', 'max:5000'],
        ]);

        $response = \DB::transaction(function () use ($data, $survey, $request): SurveyResponse {
            $resp = SurveyResponse::create([
                'survey_id' => $survey->id,
                'user_id' => $request->user()->id,
                'tgl' => now(),
            ]);

            foreach ($data['jawaban'] as $j) {
                /** @var array<string,mixed> $j */
                if (($j['isi'] ?? null) === null || $j['isi'] === '') {
                    continue;
                }

                $resp->answers()->create([
                    'survey_question_id' => $j['question_id'],
                    'jawaban' => $j['isi'],
                ]);
            }

            return $resp;
        });

        return response()->json($response->load('answers'), 201);
    }

    public function result(Survey $survey): JsonResponse
    {
        $hasil = $survey->questions->map(fn (SurveyQuestion $q): array => [
            'id' => $q->id,
            'pertanyaan' => $q->pertanyaan,
            'tipe' => $q->tipe,
            'agregat' => $q->agregat(),
        ]);

        return response()->json([
            'survey' => $survey->only(['id', 'judul', 'status']),
            'total_responden' => $survey->responses()->count(),
            'hasil' => $hasil,
        ]);
    }
}
