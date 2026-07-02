<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Política de retenção LGPD: expurga dados pessoais de registros excluídos
// há mais tempo do que o previsto em config/lgpd.php.
Schedule::command('lgpd:anonimizar')->monthly();
