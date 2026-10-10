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
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public string $subscription_id = '';

    public string $diskon = '0';

    public function issue(PlatformService $platform): void
    {
        $this->validate([
            'subscription_id' => ['required', 'integer', 'exists:subscriptions,id'],
            'diskon' => ['nullable', 'integer', 'min:0'],
        ]);

        $sub = Subscription::withoutGlobalScope(SchoolScope::class)
            ->findOrFail($this->subscription_id);

        $platform->issueInvoice($sub, auth()->user(), (int) $this->diskon);

        $this->reset(['subscription_id', 'diskon']);
        session()->flash('ok', 'Tagihan diterbitkan.');
    }

    public function markPaid(int $id, PlatformService $platform): void
    {
        $invoice = BillingInvoice::withoutGlobalScope(SchoolScope::class)->findOrFail($id);
        $platform->markInvoicePaid($invoice, auth()->user());
        session()->flash('ok', "Tagihan {$invoice->nomor} ditandai lunas.");
    }

    public function render(): View
    {
        $invoices = BillingInvoice::withoutGlobalScope(SchoolScope::class)
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('id')
            ->paginate(15);

        $subs = Subscription::withoutGlobalScope(SchoolScope::class)
            ->whereIn('status', ['trial', 'active', 'past_due'])
            ->with('plan')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $names = School::withoutGlobalScope(SchoolScope::class)
            ->whereIn('id', $subs->pluck('school_id')->merge($invoices->pluck('school_id')))
            ->pluck('nama', 'id');

        return view('livewire.platform.invoice-index', [
            'invoices' => $invoices,
            'subs' => $subs,
            'names' => $names,
        ])->layout('layouts.platform', ['title' => 'Tagihan Langganan']);
    }
}
