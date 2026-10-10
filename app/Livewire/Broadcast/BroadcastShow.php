<?php

declare(strict_types=1);

namespace App\Livewire\Broadcast;

use App\Domains\Broadcast\Services\BroadcastService;
use App\Domains\Platform\Services\QuotaService;
use App\Models\Broadcast;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Detail broadcast + log pengiriman + kirim sekarang.
 */
class BroadcastShow extends Component
{
    use WithPagination;

    public Broadcast $broadcast;

    public function mount(Broadcast $broadcast): void
    {
        $this->authorize('broadcast.view');
        $this->broadcast = $broadcast;
    }

    public function kirimSekarang(BroadcastService $service): void
    {
        $this->authorize('broadcast.kirim');

        abort_if(! app(QuotaService::class)->allows('broadcast'),
            403, 'Broadcast tidak tersedia pada masa trial.');

        abort_if(! $this->broadcast->isSiapKirim(), 422, 'Broadcast belum siap dikirim.');

        $terkirim = $service->kirim($this->broadcast);

        $this->broadcast->refresh();
        session()->flash('ok', "Broadcast dikirim ke {$terkirim} penerima.");
    }

    public function render(): View
    {
        $logs = $this->broadcast->logs()
            ->orderByDesc('dikirim_pada')
            ->paginate(20);

        return view('livewire.broadcast.broadcast-show', ['logs' => $logs])
            ->layout('layouts.app', ['title' => $this->broadcast->judul]);
    }
}
