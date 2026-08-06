<?php

namespace App\Console\Commands;

use App\Models\EmissaoFiscal;
use App\Models\User;
use App\Notifications\MdfePendenteDeEncerramentoNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('mdfe:lembrar-pendente-encerramento')]
#[Description('Avisa os admins de cada empresa sobre MDF-e autorizado cuja viagem já foi encerrada operacionalmente, mas ainda não foi encerrado na SEFAZ')]
class LembrarMdfePendenteDeEncerramento extends Command
{
    public function handle(): int
    {
        $pendentes = EmissaoFiscal::with('viagem.veiculo')
            ->where('tipo', 'mdfe')
            ->where('status', 'autorizado')
            ->whereNull('encerrado_em')
            ->whereHas('viagem', fn ($q) => $q->whereIn('status', ['aguardando_acerto', 'encerrada']))
            ->get();

        if ($pendentes->isEmpty()) {
            $this->info('Nenhum MDF-e pendente de encerramento.');

            return self::SUCCESS;
        }

        // Roda diariamente e reenvia enquanto o MDF-e não for encerrado — de
        // propósito, ao contrário de sistema:verificar-saude (que só olha a
        // última janela): aqui o problema É o silêncio (ninguém voltou pra
        // fechar o documento), então uma janela de "só o que mudou" nunca
        // pegaria nada.
        $pendentes->groupBy('empresa_id')->each(function ($emissoes, $empresaId) {
            $admins = User::where('empresa_id', $empresaId)
                ->where('role', 'admin')
                ->where('status', 'ativo')
                ->get();

            if ($admins->isEmpty()) {
                return;
            }

            Notification::send($admins, new MdfePendenteDeEncerramentoNotification($emissoes));
        });

        $this->warn("{$pendentes->count()} MDF-e(s) pendente(s) de encerramento em {$pendentes->pluck('empresa_id')->unique()->count()} empresa(s).");

        return self::SUCCESS;
    }
}
