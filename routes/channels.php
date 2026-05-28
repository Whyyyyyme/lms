<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels - LMS Praktikum
|--------------------------------------------------------------------------
*/

Broadcast::channel('lms.notifications.{userId}', function (User $user, int $userId): bool {
    return (int) $user->id === (int) $userId;
});
