<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Akademik\Services\GradeCalculationService;
use App\Domains\ERapor\Services\ERaporService;
use App\Domains\Pwa\Services\QrLoginService;
use App\Models\AcademicYear;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Tenancy\TenantContext;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ekspor PDF: cetak jadwal, leger nilai, dan rapor (US-036, US-052, US-057).
 *
 * Memakai barryvdh/laravel-dompdf dengan font DejaVu Sans (mendukung Latin).
 */
class CetakController extends Controller
{
    /**
     * Path absolut logo sekolah untuk dompdf (null bila tidak ada).
     */
    private function logoAbs(): ?string
    {
        $school = app(TenantContext::class)->school();
        $path = $school?->logo_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return null;
    }

    /**
     * Cetak jadwal mingguan per rombel (US-036).
     */
    public function jadwal(Request $request): Response
    {
        $taId = AcademicYear::where('aktif', true)->value('id');
        $rombel = Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))
            ->orderBy('nama')
            ->findOrFail($request->integer('rombel_id') ?: Rombel::when($taId, fn ($q) => $q->where('academic_year_id', $taId))->orderBy('nama')->value('id'));

        $entries = Schedule::with(['subject:id,nama,kode', 'employee:id,nama'])
            ->where('rombel_id', $rombel->id)
            ->where('academic_year_id', $taId)
            ->orderBy('jam_ke')
            ->get()
            ->groupBy('hari')
            ->map(fn ($g) => $g->keyBy('jam_ke'))
            ->all();

        $maxJam = Schedule::where('rombel_id', $rombel->id)
            ->where('academic_year_id', $taId)
            ->max('jam_ke') ?? 0;

        $grid = [];
        foreach (Schedule::HARIS as $hari) {
            $grid[$hari] = [];
            for ($j = 1; $j <= max($maxJam, 1); $j++) {
                $grid[$hari][$j] = $entries[$hari][$j] ?? null;
            }
        }

        $pdf = Pdf::loadView('pdf.jadwal', [
            'sekolah' => app(TenantContext::class)->school(),
            'rombel' => $rombel,
            'tahunAjaran' => AcademicYear::find($taId),
            'grid' => $grid,
            'maxJam' => max($maxJam, 1),
            'haris' => Schedule::HARIS,
            'logoAbs' => $this->logoAbs(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("jadwal-{$rombel->nama}.pdf");
    }

    /**
     * Cetak leger nilai per rombel + mapel + semester (US-052).
     */
    public function legerNilai(Request $request, GradeCalculationService $calc): Response
    {
        $data = $request->validate([
            'rombel_id' => ['required', 'integer'],
            'subject_id' => ['required', 'integer'],
            'semester_id' => ['required', 'integer'],
        ]);

        $rombel = Rombel::findOrFail($data['rombel_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $semester = Semester::findOrFail($data['semester_id']);

        $leger = $calc->leger($rombel->id, $subject->id, $semester->id);

        $pdf = Pdf::loadView('pdf.leger', [
            'sekolah' => app(TenantContext::class)->school(),
            'rombel' => $rombel,
            'subject' => $subject,
            'semester' => $semester,
            'leger' => $leger,
            'logoAbs' => $this->logoAbs(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("leger-{$rombel->nama}-{$subject->kode}.pdf");
    }

    /**
     * Cetak rapor satu siswa per semester (US-057).
     */
    public function rapor(Student $student, Semester $semester, ERaporService $service): Response
    {
        $rapor = $service->raporSiswa($student->id, $semester->id);

        $rombel = $student->rombels()
            ->wherePivot('academic_year_id', $semester->academic_year_id)
            ->wherePivot('status', 'aktif')
            ->first();

        $pdf = Pdf::loadView('pdf.rapor', [
            'sekolah' => $rapor['sekolah'],
            'siswa' => $student,
            'semester' => $semester,
            'rombel' => $rombel,
            'rapor' => $rapor,
            'logoAbs' => $this->logoAbs(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("rapor-{$student->nis}-{$semester->nama}.pdf");
    }

    /**
     * Cetak kartu pelajar satu siswa (foto + biodata + QR login).
     *
     * QR kartu = token QR login siswa (32-char hex, persis sama dengan QR
     * pada kartu QR login): memindainya di login PWA akan masuk sebagai
     * siswa tersebut. Mencetak kartu menerbitkan (rotasi) kredensial login
     * QR, jadi butuh izin `qr.manage`. Tanpa izin itu, kartu memakai QR
     * identifikasi `SISWA:{nis}` (tidak bisa dipakai login) agar pencetakan
     * tidak terblokir.
     */
    public function kartuPelajar(Student $student): Response
    {
        $pdf = Pdf::loadView('pdf.kartu-pelajar', [
            'sekolah' => app(TenantContext::class)->school(),
            'kartu' => [$this->kartuData($student)],
            'logoAbs' => $this->logoAbs(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("kartu-pelajar-{$student->nis}.pdf");
    }

    /**
     * Cetak kartu pelajar seluruh siswa aktif di satu rombel (8 kartu/halaman A4).
     */
    public function kartuPelajarRombel(Request $request): Response
    {
        $rombel = Rombel::findOrFail($request->integer('rombel_id'));

        $students = $rombel->students()
            ->wherePivot('academic_year_id', $rombel->academic_year_id)
            ->wherePivot('status', 'aktif')
            ->where('students.status', 'aktif')
            ->orderBy('nama')
            ->get();

        $pdf = Pdf::loadView('pdf.kartu-pelajar', [
            'sekolah' => app(TenantContext::class)->school(),
            'kartu' => $students->map(fn (Student $s) => $this->kartuData($s, $rombel))->all(),
            'logoAbs' => $this->logoAbs(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("kartu-pelajar-{$rombel->nama}.pdf");
    }

    /**
     * Data satu kartu: siswa + rombel aktif + foto absolut + QR base64.
     *
     * @return array{siswa: Student, rombel: ?Rombel, fotoAbs: ?string, qr: string, qrLogin: bool}
     */
    private function kartuData(Student $student, ?Rombel $rombel = null): array
    {
        $rombel ??= $student->rombels()
            ->wherePivot('status', 'aktif')
            ->orderByDesc('class_student.academic_year_id')
            ->first();

        $fotoAbs = $student->foto_path && Storage::disk('public')->exists($student->foto_path)
            ? Storage::disk('public')->path($student->foto_path)
            : null;

        $token = app(QrLoginService::class)->tokenForKartu($student, auth()->user());

        return [
            'siswa' => $student,
            'rombel' => $rombel,
            'fotoAbs' => $fotoAbs,
            'qr' => $this->qrPngBase64($token ?? 'SISWA:'.$student->nis, 90),
            'qrLogin' => $token !== null,
        ];
    }

    /**
     * Render QR sebagai data URI PNG (paling aman untuk dompdf).
     */
    private function qrPngBase64(string $content, int $size): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size), new ImagickImageBackEnd('png'));
        $png = (new Writer($renderer))->writeString($content);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
