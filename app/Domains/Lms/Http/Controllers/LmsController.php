<?php

declare(strict_types=1);

namespace App\Domains\Lms\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API LMS Mini v1 (modul add-on — di belakang middleware module:lms_mini).
 */
class LmsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Assignment::with(['subject:id,nama', 'rombel:id,nama'])
                ->withCount('submissions')
                ->when($request->filled('rombel_id'), fn ($q) => $q->where('rombel_id', $request->integer('rombel_id')))
                ->orderByDesc('deadline')
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:10000'],
            'deadline' => ['required', 'date'],
        ]);

        $data['dibuat_oleh'] = $request->user()->id;

        return response()->json(Assignment::create($data), 201);
    }

    public function show(Assignment $assignment): JsonResponse
    {
        return response()->json(
            $assignment->load(['subject:id,nama', 'rombel:id,nama', 'submissions.student:id,nis,nama'])
        );
    }

    public function submit(Request $request, Assignment $assignment): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'jawaban' => ['required', 'string', 'max:20000'],
        ]);

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $data['student_id']],
            ['jawaban' => $data['jawaban'], 'tgl_kumpul' => now()]
        );

        return response()->json($submission, 201);
    }

    public function grade(Request $request, AssignmentSubmission $submission): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update($data);

        return response()->json($submission->fresh());
    }
}
