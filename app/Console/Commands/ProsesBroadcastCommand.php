<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Broadcast\Services\BroadcastService;
use Illuminate\Console\Command;

/**
 * Kirim broadcast terjadwal yang sudah jatuh tempo.
 * Dijalankan berkala (mis. setiap menit) via scheduler.
 */
class ProsesBroadcastCommand extends Command
{
    protected $signature = 'broadcast:proses';

    protected $description = 'Kirim broadcast terjadwal yang sudah jatuh tempo.';

    public function handle(BroadcastService $service): int
    {
        $count = $service->prosesTerjadwal();

        $this->info("Memproses {$count} broadcast terjadwal.");

        return self::SUCCESS;
    }
}
