<?php

use App\Jobs\DeadlineReminderJob;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes - Scheduler LMS Praktikum
|--------------------------------------------------------------------------
|
| Scheduler ini menjalankan pengecekan deadline tugas.
|
| Mode testing:
| DEADLINE_REMINDER_MODE=minutes
| DEADLINE_REMINDER_AMOUNTS=3,2
| Scheduler berjalan setiap menit.
|
| Mode production:
| DEADLINE_REMINDER_MODE=days
| DEADLINE_REMINDER_AMOUNTS=3,2
| Scheduler berjalan setiap hari jam 07:00 WIB.
|
*/

$deadlineReminderMode = strtolower((string) env('DEADLINE_REMINDER_MODE', 'days'));

if ($deadlineReminderMode === 'minutes') {
    Schedule::job(new DeadlineReminderJob)
        ->everyMinute()
        ->timezone('Asia/Jakarta')
        ->name('lms-deadline-reminder-testing-minutes')
        ->withoutOverlapping();
} else {
    Schedule::job(new DeadlineReminderJob)
        ->dailyAt('07:00')
        ->timezone('Asia/Jakarta')
        ->name('lms-deadline-reminder-production-days')
        ->withoutOverlapping();
}