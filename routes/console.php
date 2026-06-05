<?php

use App\Jobs\DeadlineReminderJob;
use App\Jobs\SyncScheduledLmsItemsJob;
use App\Services\LmsNotificationService;
use App\Services\StudentAccessService;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes - Scheduler LMS Praktikum
|--------------------------------------------------------------------------
|
| Strategi ini dibuat aman untuk shared hosting seperti Hostinger.
| Cron Job cukup menjalankan Laravel Scheduler, tanpa queue worker permanen:
|
| * * * * * /usr/bin/php /path/to/project/artisan schedule:run >> /dev/null 2>&1
|
| Karena itu job penting dipanggil langsung lewat Schedule::call(), bukan
| Schedule::job() untuk job ShouldQueue. Dengan begitu reminder tetap jalan
| walaupun QUEUE_CONNECTION=sync atau tidak ada queue worker.
|
*/

Schedule::call(function (): void {
    app(SyncScheduledLmsItemsJob::class)->handle(app(StudentAccessService::class));
})
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->name('lms-sync-scheduled-items')
    ->withoutOverlapping();

$deadlineReminderMode = strtolower((string) env('DEADLINE_REMINDER_MODE', 'days'));

$deadlineReminderCallback = function (): void {
    app(DeadlineReminderJob::class)->handle(
        app(LmsNotificationService::class),
        app(StudentAccessService::class),
    );
};

if ($deadlineReminderMode === 'minutes') {
    Schedule::call($deadlineReminderCallback)
        ->everyMinute()
        ->timezone('Asia/Jakarta')
        ->name('lms-deadline-reminder-testing-minutes')
        ->withoutOverlapping();
} else {
    Schedule::call($deadlineReminderCallback)
        ->dailyAt('07:00')
        ->timezone('Asia/Jakarta')
        ->name('lms-deadline-reminder-production-days')
        ->withoutOverlapping();
}
