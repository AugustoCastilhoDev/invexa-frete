<?php

namespace App\Console\Commands;

use App\Models\LogAcesso;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('lgpd:expurgar-logs-acesso {--dry-run : Apenas informa quantos registros seriam apagados, sem alterar nada}')]
#[Description('Apaga logs de acesso à aplicação mais antigos que o prazo de retenção do Marco Civil da Internet (Art. 15)')]
class ExpurgarLogsAcesso extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $limite = now()->subMonths((int) config('lgpd.retencao_meses.logs_acesso'));

        $query = LogAcesso::withoutGlobalScope('empresa')->where('created_at', '<=', $limite);

        $total = $query->count();

        if (! $dryRun) {
            $query->delete();
        }

        $acao = $dryRun ? 'seriam apagados' : 'apagados';
        $this->info("Logs de acesso {$acao}: {$total}");

        return self::SUCCESS;
    }
}
