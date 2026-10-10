<?php

declare(strict_types=1);

namespace App\Livewire\Platform;

use App\Domains\Platform\Services\PlatformService;
use App\Models\BillingInvoice;
use App\Models\School;
use App\Models\Subscription;
use App\Scopes\SchoolScope;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(PlatformService $platform): View
    {
        $trialSoon = Subscription::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'trial')
            ->whereBetween('trial_berakhir_pada', [now(), now()->addDays(7)])
            ->with('plan')
            ->orderBy('trial_berakhir_pada')
            ->limit(8)
            ->get();

        $overdue = BillingInvoice::withoutGlobalScope(SchoolScope::class)
            ->where('status', 'overdue')
            ->orderBy('jatuh_tempo')
            ->limit(8)
            ->get();

        return view('livewire.platform.dashboard', [
            'metrics' => $platform->metrics(),
            'trialSoon' => $trialSoon,
            'overdue' => $overdue,
        ])->layout('layouts.platform', ['title' => 'Dashboard Platform']);
    }

    public function schoolName(int $schoolId): string
    {
        return School::withoutGlobalScope(SchoolScope::class)->whereKey($schoolId)->value('nama') ?? '—';
    }
}
