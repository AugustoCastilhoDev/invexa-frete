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

// Marco Civil da Internet (Art. 15): apaga logs de acesso à aplicação mais
// antigos que o prazo mínimo de retenção previsto em config/lgpd.php.
Schedule::command('lgpd:expurgar-logs-acesso')->monthly();

// Backup diário do banco de dados (+ uploads locais, se houver), com cópia
// local e outra na R2 (fora do servidor). Roda de madrugada, horário de
// menor uso; limpeza antes do backup, monitor depois pra alertar por e-mail
// se algo ficar velho/quebrado (ver config/backup.php).
Schedule::command('backup:clean')->daily()->at('01:30');
Schedule::command('backup:run')->daily()->at('01:40');
Schedule::command('backup:monitor')->daily()->at('02:10');
