<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Billing\Services\DunningService;
use Illuminate\Console\Command;

/**
 * Siklus hidup langganan harian: pengingat trial, ekspirasi,
 * suspend otomatis, invoice overdue, dan perpanjangan.
 * Dijadwalkan di routes/console.php (daily 08:00).
 */
class SubscriptionLifecycleCommand extends Command
{
    protected $signature = 'subscription:lifecycle';

    protected $description = 'Proses siklus hidup langganan: trial, dunning, overdue, perpanjangan';

    public function handle(DunningService $dunning): int
    {
        $hasil = $dunning->proses();

        foreach ($hasil as $jenis => $jumlah) {
            $this->info("{$jenis}: {$jumlah}");
        }

        return self::SUCCESS;
    }
}
