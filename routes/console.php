<?php

use Illuminate\Support\Facades\Schedule;

// On a normal server (cPanel / VPS) add one cron line: * * * * * php artisan schedule:run
// On Vercel the /cron/ebay-sync URL is called instead (see EBAY_SETUP.md).
Schedule::command('ebay:sync')->everyFifteenMinutes()->withoutOverlapping();
