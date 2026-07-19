<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Command berjalan setiap menit karena setiap transaksi mempunyai scheduled_at sendiri.
// withoutOverlapping mencegah dua proses scheduler mengirim jadwal yang sama bersamaan.
Schedule::command('crm:send-retention-whatsapp')
    ->everyMinute()
    ->withoutOverlapping();
