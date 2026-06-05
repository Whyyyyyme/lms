<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LMS Notification Queue Strategy
    |--------------------------------------------------------------------------
    |
    | Default dibuat aman untuk shared hosting/Hostinger: notifikasi in-app
    | langsung disimpan saat request/scheduler berjalan, sehingga tidak wajib
    | menjalankan queue worker terus-menerus.
    |
    | Jika nanti memakai VPS/worker queue, ubah:
    | LMS_NOTIFICATION_QUEUE_IN_APP=true
    | QUEUE_CONNECTION=database/redis
    |
    */

    'queue_in_app' => env('LMS_NOTIFICATION_QUEUE_IN_APP', false),

    'queue_name' => env('LMS_NOTIFICATION_QUEUE_NAME', 'notifications'),

    /*
    |--------------------------------------------------------------------------
    | Email Notification
    |--------------------------------------------------------------------------
    |
    | Ini hanya mengatur email notifikasi LMS seperti reminder deadline.
    | Email verifikasi/aktivasi akun mahasiswa tidak disentuh oleh config ini.
    |
    */

    'mail_enabled' => env('LMS_NOTIFICATION_MAIL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Broadcast / Realtime Notification
    |--------------------------------------------------------------------------
    |
    | Default mengikuti BROADCAST_CONNECTION. Jika masih log/null, broadcast
    | dimatikan agar sistem tidak terlihat seolah realtime aktif padahal belum.
    | Aktifkan setelah Echo + Pusher/Reverb sudah benar-benar siap.
    |
    */

    'broadcast_enabled' => env(
        'LMS_NOTIFICATION_BROADCAST_ENABLED',
        ! in_array(env('BROADCAST_CONNECTION', 'null'), ['null', 'log'], true)
    ),

    /*
    |--------------------------------------------------------------------------
    | Fallback Polling Navbar Notification
    |--------------------------------------------------------------------------
    |
    | Jika Echo belum tersedia, dropdown notifikasi akan refresh berkala.
    | Ini membantu di shared hosting yang belum memakai websocket.
    |
    */

    'polling_enabled' => env('LMS_NOTIFICATION_POLLING_ENABLED', true),

    'polling_interval_ms' => (int) env('LMS_NOTIFICATION_POLLING_INTERVAL_MS', 30000),
];
