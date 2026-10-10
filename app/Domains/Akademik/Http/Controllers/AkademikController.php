<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Http\Controllers;

use App\Domains\Akademik\Services\ScheduleConflictService;
use App\Models\Attendance;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * API akademik v1: mapel, jadwal, absensi (FR-402–406, FR-501–506, FR-601–606).
 */
class AkademikController extends Controller
{
    // ---------- Mata pelajaran ----------

    public function subjectIndex(Request $request): JsonResponse
    {
        return response()->json(
            Subject::when($request->string('search')->isNotEmpty(), fn ($q) => $q->where('nama', 'like', '%'.$request->string('search').'%'))
                ->orderBy('nama')->paginate(20)
        );
    }

    public function subjectStore(Request $request): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:20', Rule::unique('subjects')->where(fn ($q) => $q->where('school_id', $schoolId))],
            'nama' => ['required', 'string', 'max:100'],
            'kelompok' => ['required', Rule::in(['wajib', 'peminatan', 'mulok'])],
            'kkm_default' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        return response()->json(Subject::create($data), 201);
    }

    // ---------- Jadwal ----------

    public function scheduleIndex(Request $request): JsonResponse
    {
        return response()->json(
            Schedule::with(['subject:id,kode,nama', 'employee:id,nama', 'rombel:id,nama'])
                ->when($request->integer('rombel_id'), fn ($q, $id) => $q->where('rombel_id', $id))
                ->when($request->string('hari')->isNotEmpty(), fn ($q) => $q->where('hari', $request->string('hari')))
                ->orderBy('hari')->orderBy('jam_ke')
                ->paginate(20)
        );
    }

    public function scheduleStore(Request $request, ScheduleConflictService $conflict): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'hari' => ['required', Rule::in(Schedule::HARIS)],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:15'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'ruang' => ['nullable', 'string', 'max:30'],
        ]);

        /** @var array{rombel_id:int, academic_year_id:int, hari:string, jam_ke:int, employee_id:int, ruang:?string} $check */
        $check = [
            'rombel_id' => (int) $data['rombel_id'],
            'academic_year_id' => (int) $data['academic_year_id'],
            'hari' => (string) $data['hari'],
            'jam_ke' => (int) $data['jam_ke'],
            'employee_id' => (int) $data['employee_id'],
            'ruang' => $data['ruang'] ?? null,
        ];

        $conflicts = $conflict->check($check);
        if ($conflicts !== []) {
            return response()->json([
                'message' => 'Jadwal bentrok.',
                'errors' => ['jadwal' => $conflicts],
            ], 422);
        }

        return response()->json(Schedule::create($data), 201);
    }

    public function scheduleDestroy(Schedule $schedule): JsonResponse
    {
        $schedule->delete();

        return response()->json(['message' => 'Jadwal dihapus.']);
    }

    // ---------- Absensi ----------

    public function attendanceIndex(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'tanggal' => ['required', 'date'],
        ]);

        return response()->json(
            Attendance::with('student:id,nama,nis')
                ->where('rombel_id', $v['rombel_id'])
                ->whereDate('tanggal', $v['tanggal'])
                ->orderBy('id')
                ->paginate(50)
        );
    }

    public function attendanceStore(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'tanggal' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', Rule::in(['H', 'I', 'S', 'A'])],
            'records.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $locked = Attendance::where('rombel_id', $v['rombel_id'])
            ->whereDate('tanggal', $v['tanggal'])
            ->whereNotNull('locked_at')
            ->exists();

        if ($locked) {
            return response()->json(['message' => 'Absensi sudah dikunci.'], 423);
        }

        /** @var array<int,array{student_id:int,status:string,keterangan:?string}> $records */
        $records = $v['records'];
        $tanggal = Carbon::parse($v['tanggal'])->toDateString();

        foreach ($records as $r) {
            Attendance::updateOrCreate(
                ['student_id' => $r['student_id'], 'tanggal' => $tanggal],
                [
                    'rombel_id' => $v['rombel_id'],
                    'status' => $r['status'],
                    'keterangan' => $r['keterangan'] ?? null,
                    'input_by' => $request->user()->id,
                ],
            );
        }

        return response()->json(['message' => 'Absensi tersimpan.', 'count' => count($records)]);
    }

    public function attendanceLock(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'tanggal' => ['required', 'date'],
        ]);

        $count = Attendance::where('rombel_id', $v['rombel_id'])
            ->whereDate('tanggal', $v['tanggal'])
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        return response()->json(['message' => 'Absensi dikunci.', 'count' => $count]);
    }

    /**
     * Rekap kehadiran satu rombel dalam rentang tanggal (untuk laporan wali kelas).
     */
    public function attendanceSummary(Request $request): JsonResponse
    {
        /** @var array<string,mixed> $v */
        $v = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombels,id'],
            'dari' => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
        ]);

        $rombel = Rombel::findOrFail($v['rombel_id']);

        $rows = Attendance::where('rombel_id', $rombel->id)
            ->whereDate('tanggal', '>=', $v['dari'])
            ->whereDate('tanggal', '<=', $v['sampai'])
            ->selectRaw('student_id, status, COUNT(*) as jumlah')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id');

        $summary = $rombel->students()->orderBy('nama')->get()->map(function (Student $s) use ($rows): array {
            /** @var Collection<int, Attendance> $perSiswa */
            $perSiswa = $rows[$s->id] ?? collect();

            $hitung = fn (string $status): int => (int) ($perSiswa->firstWhere('status', $status)?->getAttribute('jumlah') ?? 0);

            return [
                'student_id' => $s->id,
                'nama' => $s->nama,
                'nis' => $s->nis,
                'H' => $hitung('H'),
                'I' => $hitung('I'),
                'S' => $hitung('S'),
                'A' => $hitung('A'),
            ];
        });

        return response()->json(['data' => $summary]);
    }
}
