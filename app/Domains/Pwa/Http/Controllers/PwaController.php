<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Http\Controllers;

use App\Domains\Pwa\Services\PwaContext;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
}
