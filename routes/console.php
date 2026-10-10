<?php

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    /** @var Command $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('broadcast:proses')->everyMinute();

// Siklus hidup langganan: trial, dunning, overdue, perpanjangan.
Schedule::command('subscription:lifecycle')->dailyAt('08:00');
