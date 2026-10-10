<?php

declare(strict_types=1);

namespace App\Domains\Laporan\Http\Controllers;

use App\Domains\Laporan\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * API Laporan v1 (FR-1001/1002).
 */
class ReportController extends Controller
{
    public function index(Request $request, ReportService $service): JsonResponse
    {
        /** @var array<string,mixed> $data */
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(ReportService::TYPES))],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
        ]);

        return response()->json($service->generate($data['type'], $data));
    }

    public function export(Request $request, ReportService $service): StreamedResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(ReportService::TYPES))],
        ]);

        $report = $service->generate($request->string('type')->toString(), $request->only(['q', 'status', 'dari', 'sampai', 'semester_id']));
        $csv = $service->toCsv($report);

        return response()->streamDownload(
            fn () => print ($csv),
            'laporan-'.$request->string('type').'-'.now()->format('Ymd-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
