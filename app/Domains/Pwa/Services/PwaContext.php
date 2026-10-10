<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Konteks PWA per request: peran aktif (siswa/orang-tua), daftar anak,
 * dan siswa aktif (untuk orang tua: anak terpilih — mode profil anak,
 * Dok 09 §3.4: read-only, tercatat atas nama akun orang tua).
 */
class PwaContext
{
    public const SESSION_CHILD = 'pwa_child_id';

    private ?User $user = null;

    private ?string $role = null;

    /** @var Collection<int, Student>|null */
    private ?Collection $children = null;

    private ?Student $activeStudent = null;

    private bool $resolved = false;

    public function user(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->resolved = false;
    }

    /**
     * Peran PWA: 'siswa' | 'orang-tua' | null (belum terhubung).
     */
    public function role(): ?string
    {
        $this->resolve();

        return $this->role;
    }

    public function isLinked(): bool
    {
        return $this->role() !== null;
    }

    /**
     * Siswa aktif dalam konteks (milik sendiri / anak terpilih).
     */
    public function activeStudent(): ?Student
    {
        $this->resolve();

        return $this->activeStudent;
    }

    /**
     * @return Collection<int, Student>
     */
    public function children(): Collection
    {
        $this->resolve();

        return $this->children ?? collect();
    }

    /**
     * Beralih ke mode profil anak (Dok 09 §3.4).
     */
    public function switchChild(int $studentId): bool
    {
        $this->resolve();

        if ($this->role !== 'orang-tua') {
            return false;
        }

        $child = $this->children?->firstWhere('id', $studentId);

        if ($child === null) {
            return false;
        }

        session([self::SESSION_CHILD => $studentId]);
        $this->activeStudent = $child;

        return true;
    }

    private function resolve(): void
    {
        if ($this->resolved || $this->user === null) {
            return;
        }

        $this->resolved = true;
        $user = $this->user;

        if ($user->student_id !== null) {
            $student = Student::find($user->student_id);

            if ($student !== null) {
                $this->role = 'siswa';
                $this->activeStudent = $student;
                $this->children = collect([$student]);

                return;
            }
        }

        $guardians = $user->guardians()->with('students')->get();

        if ($guardians->isNotEmpty()) {
            $children = $guardians->flatMap(fn ($g) => $g->students)->unique('id')->values();

            if ($children->isNotEmpty()) {
                $this->role = 'orang-tua';
                $this->children = $children;

                $childId = session(self::SESSION_CHILD);
                $this->activeStudent = $children->firstWhere('id', (int) $childId) ?? $children->first();

                return;
            }
        }

        $this->role = null;
        $this->children = collect();
        $this->activeStudent = null;
    }
}
