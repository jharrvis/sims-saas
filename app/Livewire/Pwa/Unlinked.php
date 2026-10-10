<?php

declare(strict_types=1);

namespace App\Livewire\Pwa;

use Illuminate\View\View;
use Livewire\Component;

/**
 * Akun belum terhubung ke data siswa/wali.
 */
class Unlinked extends Component
{
    public function render(): View
    {
        return view('livewire.pwa.unlinked')->layout('layouts.pwa', [
            'title' => 'Belum Terhubung',
            'hideNav' => true,
        ]);
    }
}
