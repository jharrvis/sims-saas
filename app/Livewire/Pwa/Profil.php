<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use App\Domains\Pwa\Services\PwaContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Profil & Perangkat — P-06. Info akun, perangkat aktif (token), keluar.
 */
class Profil extends Component
{
    public function revokeToken(int $tokenId): void
    {
        $token = auth()->user()?->tokens()->whereKey($tokenId)->first();

        $token?->delete();
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('pwa.login'), navigate: true);
    }

    public function render(PwaContext $pwa): View
    {
        $user = auth()->user();
        $student = $pwa->activeStudent();

        return view('livewire.pwa.profil', [
            'user' => $user,
            'role' => $pwa->role(),
            'student' => $student,
            'children' => $pwa->children(),
            'tokens' => $user?->tokens()->orderByDesc('last_used_at')->get() ?? collect(),
        ])->layout('layouts.pwa', [
            'title' => 'Profil',
            'schoolName' => $student?->school?->nama,
            'showChildSwitcher' => $pwa->role() === 'orang-tua',
            'children' => $pwa->children(),
            'activeStudentId' => $student?->id,
        ]);
    }
}
