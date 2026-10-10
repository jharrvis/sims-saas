<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Http\Controllers;

use App\Domains\Pwa\Services\PwaIdentityService;
use App\Domains\Pwa\Services\QrLoginService;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

/**
 * Endpoint QR login (Dok 09 §8).
 */
class QrApiController extends Controller
{
    /**
     * POST /api/v1/auth/qr/challenge — buat tantangan QR (guru/admin).
     */
    public function challenge(Request $request, QrLoginService $qr): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $hasil = $qr->createChallenge($request->user(), Student::findOrFail($data['student_id']));

        return response()->json([
            'token' => $hasil['token'],
            'expires_at' => $hasil['expires_at']->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v1/auth/qr/redeem — tukar token QR menjadi token API terbatas
     * (8 jam, tanpa eskalasi hak).
     */
    public function redeem(Request $request, QrLoginService $qr, PwaIdentityService $identity): JsonResponse
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

        $token = $user->createToken('qr-login', ['*'], now()->addHours(QrLoginService::SESSION_TTL_HOURS));

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'student' => ['id' => $student->id, 'nama' => $student->nama],
        ]);
    }

    /**
     * POST /api/v1/auth/qr/revoke-card — cabut kartu QR siswa (hilang).
     */
    public function revokeCard(Request $request, QrLoginService $qr): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        return response()->json([
            'revoked' => $qr->revokeCard(Student::findOrFail($data['student_id'])),
        ]);
    }
}
