<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Mail::raw(
    'Test bevestigingsmail vanuit Polls Elementa: dit is een echte SMTP test.',
    function ($message): void {
        $message->to('mikcel73@gmail.com')->subject('Test mail Polls Elementa');
    }
);

echo "MAIL_SENT\n";
