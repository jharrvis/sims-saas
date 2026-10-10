<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Services;

use App\Models\QrChallenge;
use App\Models\QrLoginCard;
use App\Models\Student;
use App\Models\User;
use App\Scopes\SchoolScope;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;

/**
 * QR Login untuk siswa (Dok 09 §3.3):
 * - Kartu cetak: token 128-bit per siswa, berlaku satu semester, dapat dicabut.
 * - Tantangan layar: 128-bit, sekali pakai, 60 detik.
 * - Redeem → sesi perangkat bersama: 8 jam, tanpa remember-me, tanpa eskalasi hak.
 */
class QrLoginService
{
    public const CHALLENGE_TTL_SECONDS = 60;

    public const SESSION_TTL_HOURS = 8;

    /**
     * Terbitkan kartu baru (kartu lama dinonaktifkan + sesinya dicabut).
     * Kembalikan token plaintext — hanya diketahui saat ini, yang disimpan
     * hanya hash-nya.
     */
    public function issueCard(Student $student, User $by): string
    {
        $this->deactivateCards($student);

        $token = bin2hex(random_bytes(16)); // 128-bit

        QrLoginCard::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'token_hash' => hash('sha256', $token),
            'aktif' => true,
            'issued_at' => now(),
            'issued_by' => $by->id,
        ]);

        return $token;
    }

    /**
     * Cabut kartu siswa (hilang) — sesi QR yang memakai kartu ini ikut gugur.
     */
    public function revokeCard(Student $student): bool
    {
        $revoked = QrLoginCard::where('student_id', $student->id)
            ->where('aktif', true)
            ->update(['aktif' => false, 'revoked_at' => now()]) > 0;

        if ($revoked) {
            $this->wipeQrSessions($student);
        }

        return $revoked;
    }

    /**
     * Nonaktifkan semua kartu aktif siswa + cabut sesi QR-nya
     * (dipakai saat rotasi/penerbitan ulang).
     */
    private function deactivateCards(Student $student): void
    {
        QrLoginCard::where('student_id', $student->id)
            ->where('aktif', true)
            ->update(['aktif' => false, 'revoked_at' => now()]);

        $this->wipeQrSessions($student);
    }

    /**
     * Cabut sesi API (Sanctum) hasil QR login. Sesi web ditangani
     * middleware qr.limit via pemeriksaan kartu.
     */
    private function wipeQrSessions(Student $student): void
    {
        User::where('student_id', $student->id)
            ->get()
            ->each(fn (User $user) => $user->tokens()->where('name', 'qr-login')->delete());
    }

    /**
     * Buat tantangan QR dinamis (layar kelas) untuk satu siswa.
     *
     * @return array{token: string, expires_at: Carbon}
     */
    public function createChallenge(User $by, Student $student): array
    {
        $token = bin2hex(random_bytes(16)); // 128-bit
        $expiresAt = now()->addSeconds(self::CHALLENGE_TTL_SECONDS);

        QrChallenge::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
            'created_by' => $by->id,
        ]);

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    /**
     * Tukar token QR (kartu/tantangan) menjadi siswa. Tantangan ditandai terpakai.
     *
     * Pencarian memakai kunci global eksplisit (token 128-bit yang tak dapat
     * ditebak): tenant scope dilepas karena redeem terjadi sebagai tamu yang
     * belum punya tenant. Kembalikan juga id kartu untuk pelacakan revokasi.
     *
     * @return array{student: Student, card_id: int|null}|null
     */
    public function redeem(string $token): ?array
    {
        $hash = hash('sha256', $token);

        $challenge = QrChallenge::withoutGlobalScope(SchoolScope::class)
            ->where('token_hash', $hash)
            ->first();

        if ($challenge !== null) {
            if (! $challenge->isValid()) {
                return null;
            }

            $challenge->update(['used_at' => now()]);

            $student = Student::withoutGlobalScope(SchoolScope::class)->find($challenge->student_id);

            return $student ? ['student' => $student, 'card_id' => null] : null;
        }

        $card = QrLoginCard::withoutGlobalScope(SchoolScope::class)
            ->where('token_hash', $hash)
            ->first();

        if ($card === null || ! $card->isValid()) {
            return null;
        }

        $student = Student::withoutGlobalScope(SchoolScope::class)->find($card->student_id);

        return $student ? ['student' => $student, 'card_id' => $card->id] : null;
    }

    /**
     * Render QR sebagai SVG inline (bacon-qr-code, dipakai juga di 2FA).
     */
    public static function qrSvg(string $content, int $size = 220): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($content);
    }
}
