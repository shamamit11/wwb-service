<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Baseline operational schedules. Future SEO and AI workflows should add
// small orchestration commands here rather than embedding heavy logic.
Schedule::command('queue:prune-batches --hours=48')
    ->dailyAt('01:00')
    ->withoutOverlapping();

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('01:30')
    ->withoutOverlapping();
