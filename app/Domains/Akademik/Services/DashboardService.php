<?php

declare(strict_types=1);

namespace App\Domains\Akademik\Services;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\Student;
use App\Tenancy\TenantContext;

/**
 * Ringkasan dashboard sekolah.
 *
 * Semua query berjalan di bawah SchoolScope (fail-closed). Tidak ada akses
 * lintas tenant atau data demo: nilai yang tidak tersedia dikembalikan sebagai
 * nol / koleksi kosong agar UI dapat menampilkan empty state yang jujur.
 */
final class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function data($user = null): array
    {
        $school = app(TenantContext::class)->school();

        if ($school === null) {
            return $this->empty();
        }

        $today = now()->startOfDay();

        $totalStudents = Student::where('status', 'aktif')->count();
        $totalEmployees = Employee::where('status_aktif', true)->count();

        $attendanceCounts = Attendance::whereDate('tanggal', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $attendanceTotal = (int) $attendanceCounts->sum();
        $present = (int) ($attendanceCounts['H'] ?? 0);
        $attendancePercent = $attendanceTotal > 0 ? (int) round(($present / $attendanceTotal) * 100) : 0;

        $invoiceQuery = Invoice::whereIn('status', ['unpaid', 'partial', 'overdue']);
        $unpaidInvoices = (clone $invoiceQuery)->count();
        $unpaidTotal = (float) (clone $invoiceQuery)->sum('nominal') - (float) (clone $invoiceQuery)->sum('dibayar');

        $monthInvoices = Invoice::whereBetween('created_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]);
        $monthTotal = (float) (clone $monthInvoices)->sum('nominal');
        $monthPaid = (float) (clone $monthInvoices)->sum('dibayar');
        $paidPercent = $monthTotal > 0 ? (int) round(($monthPaid / $monthTotal) * 100) : 0;

        $classDistribution = Rombel::withCount('students')
            ->orderBy('nama')
            ->limit(8)
            ->get()
            ->map(fn (Rombel $rombel): array => [
                'label' => $rombel->nama,
                'value' => (int) $rombel->students_count,
            ]);

        $attendanceTrend = collect(range(6, 0))->map(function (int $daysAgo): array {
            $date = now()->subDays($daysAgo)->startOfDay();
            $counts = Attendance::whereDate('tanggal', $date)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $total = (int) $counts->sum();
            $present = (int) ($counts['H'] ?? 0);

            return [
                'label' => $date->locale('id')->isoFormat('D MMM'),
                'value' => $total > 0 ? (int) round(($present / $total) * 100) : 0,
                'total' => $total,
            ];
        });

        $todayName = strtolower(now()->locale('id')->dayName);
        $schedule = in_array($todayName, Schedule::HARIS, true)
            ? Schedule::with(['subject:id,nama', 'employee:id,nama', 'rombel:id,nama'])
                ->where('hari', $todayName)
                ->orderBy('jam_ke')
                ->limit(6)
                ->get()
            : collect();

        return [
            'school' => $school,
            'total_students' => $totalStudents,
            'total_employees' => $totalEmployees,
            'attendance_today' => $attendanceTotal,
            'attendance_present' => $present,
            'attendance_percent' => $attendancePercent,
            'unpaid_invoices' => $unpaidInvoices,
            'unpaid_total' => $unpaidTotal,
            'month_total' => $monthTotal,
            'month_paid' => $monthPaid,
            'paid_percent' => $paidPercent,
            'class_distribution' => $classDistribution,
            'attendance_trend' => $attendanceTrend,
            'recent_students' => Student::latest()->limit(5)->get(),
            'announcements' => Announcement::tayang()
                ->orderByDesc('penting')
                ->orderByDesc('tayang_dari')
                ->limit(4)
                ->get(),
            'schedule' => $schedule,
            'activities' => AuditLog::orderByDesc('id')->limit(5)->get(),
        ];
    }

    /**
     * Bentuk data yang aman saat konteks tenant belum tersedia.
     *
     * @return array<string, mixed>
     */
    private function empty(): array
    {
        return [
            'school' => null,
            'total_students' => 0,
            'total_employees' => 0,
            'attendance_today' => 0,
            'attendance_present' => 0,
            'attendance_percent' => 0,
            'unpaid_invoices' => 0,
            'unpaid_total' => 0,
            'month_total' => 0,
            'month_paid' => 0,
            'paid_percent' => 0,
            'class_distribution' => collect(),
            'attendance_trend' => collect(),
            'recent_students' => collect(),
            'announcements' => collect(),
            'schedule' => collect(),
            'activities' => collect(),
        ];
    }
}
