<?php

use App\Jobs\DeadlineReminderJob;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes - Scheduler LMS Praktikum
|--------------------------------------------------------------------------
|
| Scheduler ini menjalankan pengecekan deadline tugas setiap hari.
| Job akan mengirim notifikasi H-3 dan H-1 ke mahasiswa yang belum submit.
|
*/

Schedule::job(new DeadlineReminderJob)
    ->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->name('lms-deadline-reminder')
    ->withoutOverlapping();
