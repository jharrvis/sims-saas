<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Http\Controllers;

use App\Domains\Pwa\Services\PwaContext;
use App\Domains\Pwa\Services\PwaIdentityService;
use App\Domains\Pwa\Services\QrLoginService;
use App\Models\AuditLog;
use App\Models\Rombel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Aksi non-Livewire PWA: ganti profil anak, keluar, halaman offline.
 */
class PwaController extends Controller
{
    /**
     * Beralih mode profil anak (Dok 09 §3.4) — tercatat atas nama orang tua.
     */
    public function switchChild(Request $request, PwaContext $pwa): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $pwa->setUser($user);

        if (! $pwa->switchChild($data['student_id'])) {
            abort(403, 'Anak tidak terhubung ke akun ini.');
        }

        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => 'pwa_switch_child',
            'model_type' => User::class,
            'model_id' => (string) $user->id,
            'after' => ['student_id' => $data['student_id']],
            'ip_address' => $request->ip(),
        ]);

        return back();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pwa.login');
    }

    public function offline(): View
    {
        return view('pwa.offline');
    }

    public function serviceWorker(): BinaryFileResponse
    {
        $path = public_path('pwa-assets/sw.js');

        abort_unless(is_file($path), 404);

        $response = response()->file($path, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => '/pwa',
        ]);

        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');

        return $response;
    }

    /**
     * Tukar token QR (kartu/tantangan) menjadi sesi perangkat bersama (Dok 09 §3.3):
     * 8 jam, tanpa remember-me, tanpa eskalasi hak (peran siswa saja).
     */
    public function qrRedeem(Request $request, QrLoginService $qr, PwaIdentityService $identity): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:32'],
        ]);

        $hasil = $qr->redeem($data['token']);

        if ($hasil === null) {
            throw ValidationException::withMessages([
                'token' => 'Kode QR tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        $student = $hasil['student'];

        $user = User::where('student_id', $student->id)->first();

        if ($user === null) {
            $user = User::create([
                'username' => 'qr-siswa-'.$student->id,
                'name' => $student->nama,
            ]);
        }

        $identity->linkStudent($user, $student);

        Auth::login($user, false);
        $request->session()->regenerate();

        $now = now()->timestamp;
        $request->session()->put([
            'qr_limited' => true,
            'qr_expires_at' => $now + QrLoginService::SESSION_TTL_HOURS * 3600,
            'qr_card_id' => $hasil['card_id'],
            'qr_redeemed_at' => $now,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => 'qr_redeem',
            'model_type' => Student::class,
            'model_id' => (string) $student->id,
            'after' => ['school_id' => $student->school_id],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['ok' => true, 'redirect' => route('pwa.home')]);
    }

    /**
     * Cetak kartu QR (admin): memakai token plaintext yang baru diterbitkan,
     * tersimpan di sesi (tidak pernah disimpan di database).
     */
    public function qrCetak(Request $request): View
    {
        $tokens = session('qr_fresh_tokens', []);
        $ids = [];

        if ($request->filled('student_id')) {
            $ids = [(int) $request->query('student_id')];
        } elseif ($request->filled('rombel_id')) {
            $ids = Rombel::findOrFail($request->query('rombel_id'))
                ->students()->wherePivot('status', 'aktif')->pluck('students.id')->all();
        }

        $kartu = Student::whereIn('id', $ids)->orderBy('nama')->get()
            ->filter(fn (Student $s) => isset($tokens[$s->id]))
            ->map(fn (Student $s) => ['student' => $s, 'token' => $tokens[$s->id]]);

        return view('qr.cetak', ['kartu' => $kartu]);
    }
}
