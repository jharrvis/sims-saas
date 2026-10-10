<?php

declare(strict_types=1);

namespace App\Domains\Pwa\Jobs;

use App\Domains\Pwa\Services\PushService;
use App\Models\PushSubscription;
use App\Scopes\SchoolScope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Kirim satu Web Push notification ke satu subscription.
 */
class SendPushNotification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{title:string, body:string, icon:string, url:string}  $payload
     */
    public function __construct(
        public int $subscriptionId,
        public array $payload,
    ) {}

    public function handle(PushService $push): void
    {
        // Tanpa tenant scope: job jalan di queue worker tanpa konteks tenant;
        // id subscription adalah referensi internal hasil dispatch kita sendiri.
        $sub = PushSubscription::withoutGlobalScope(SchoolScope::class)->find($this->subscriptionId);

        if ($sub === null || ! $push->enabled()) {
            return;
        }

        $push->deliver($sub, $this->payload);
    }
}
