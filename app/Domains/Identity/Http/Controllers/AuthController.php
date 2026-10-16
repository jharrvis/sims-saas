<?php

declare(strict_types=1);

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Services\OtpRateLimited;
use App\Domains\Identity\Services\OtpService;
use App\Domains\Identity\Services\TotpService;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

/**
 * API autentikasi v1 (Dok 03, Dok 09).
 */
class AuthController extends Controller
{
    /**
     * Minta kode OTP ke nomor WA.
     */
    public function requestOtp(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'keperluan' => ['sometimes', 'string', 'in:login,aktivasi_pwa,reset_password'],
        ]);

        $keperluan = $data['keperluan'] ?? 'login';
        $schoolName = $request->attributes->get('school_name', 'SIMS');

        try {
            $otp->request($data['phone'], $keperluan, $schoolName);
        } catch (OtpRateLimited $e) {
            throw ValidationException::withMessages(['phone' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Kode OTP telah dikirim via WhatsApp.']);
    }

    /**
     * Verifikasi OTP → login. Menerbitkan token Sanctum + daftar membership.
     */
    public function verifyOtp(Request $request, OtpService $otp, TenantContext $tenant): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (! $otp->verify($data['phone'], 'login', $data['code'])) {
            $this->audit($request, null, 'login_gagal', ['phone' => $data['phone']]);

            throw ValidationException::withMessages(['code' => 'Kode salah atau kedaluwarsa.']);
        }

        $user = User::where('phone', $this->normalize($data['phone']))->first();

        if (! $user) {
            throw ValidationException::withMessages(['phone' => 'Nomor belum terdaftar.']);
        }

        // 2FA opt-in: bila user mengaktifkan 2FA → minta langkah verifikasi.
        if ($user->mfaRequired() && ! $this->mfaVerified($request, $user)) {
            return response()->json([
                'mfa_required' => true,
                'enrollment_required' => ! $user->hasMfaEnabled(),
                'user_id' => $user->id,
            ], 202);
        }

        return $this->issueToken($request, $user, $tenant);
    }

    /**
     * Verifikasi TOTP untuk peran platform (langkah ke-2 login).
     */
    public function verifyMfa(Request $request, TotpService $totp, TenantContext $tenant): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'code' => ['required', 'string'],
        ]);

        $user = User::findOrFail($data['user_id']);

        $valid = $totp->verify($user, $data['code'])
            || $totp->useRecoveryCode($user, $data['code']);

        if (! $valid) {
            $this->audit($request, $user->id, 'mfa_gagal', []);

            throw ValidationException::withMessages(['code' => 'Kode MFA salah.']);
        }

        $request->session()->put('mfa_verified_'.$user->id, true);

        return $this->issueToken($request, $user, $tenant);
    }

    /**
     * Ganti tenant aktif (untuk user dengan banyak membership).
     */
    public function switchTenant(Request $request, TenantContext $tenant): JsonResponse
    {
        $data = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
        ]);

        $user = $request->user();

        abort_unless(
            $user->activeMemberships()->where('school_id', $data['school_id'])->exists(),
            403,
            'BUKAN_ANGGOTA_TENANT'
        );

        // Cabut semua token lama, terbitkan baru dengan tenant baru
        $user->tokens()->delete();

        $school = School::findOrFail($data['school_id']);
        $tenant->set($school);

        $token = $user->createToken('pwa', ['school:'.$school->id])->plainTextToken;

        $this->audit($request, $user->id, 'switch_tenant', ['school_id' => $school->id]);

        return response()->json([
            'token' => $token,
            'school' => $school->only(['id', 'nama', 'slug', 'jenjang']),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->only(['id', 'username', 'name', 'email', 'phone', 'is_platform']),
            'memberships' => $user->activeMemberships()->with('school:id,nama,slug,jenjang')->get()
                ->map(fn ($m) => $m->school),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var mixed $token */
        $token = $request->user()->currentAccessToken();

        if (is_object($token) && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    /**
     * Terbitkan token Sanctum dengan satu tenant aktif (ADR-0004).
     */
    private function issueToken(Request $request, User $user, TenantContext $tenant): JsonResponse
    {
        $membership = $user->activeMemberships()->with('school')->first();

        $abilities = ['*'];
        if ($membership) {
            $tenant->set($membership->school);
            $abilities = ['school:'.$membership->school_id];
        }

        $token = $user->createToken('pwa', $abilities)->plainTextToken;

        $this->audit($request, $user->id, 'login', [
            'school_id' => $membership?->school_id,
            'via' => 'wa_otp',
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'username', 'name', 'email', 'phone', 'is_platform']),
            'school' => $membership?->school->only(['id', 'nama', 'slug', 'jenjang']),
            'memberships' => $user->activeMemberships()->with('school:id,nama,slug,jenjang')->get()
                ->map(fn ($m) => $m->school),
        ]);
    }

    private function mfaVerified(Request $request, User $user): bool
    {
        return (bool) $request->session()->get('mfa_verified_'.$user->id, false);
    }

    /**
     * @param  array<string,mixed>  $after
     */
    private function audit(Request $request, ?int $userId, string $aksi, array $after): void
    {
        AuditLog::create([
            'school_id' => app(TenantContext::class)->schoolId(),
            'user_id' => $userId,
            'aksi' => $aksi,
            'model_type' => User::class,
            'model_id' => (string) ($userId ?? 0),
            'after' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
        ]);
    }

    private function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}
