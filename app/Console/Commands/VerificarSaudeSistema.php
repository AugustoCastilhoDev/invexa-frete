<?php

namespace App\Console\Commands;

use App\Models\EmissaoFiscal;
use App\Notifications\SaudeSistemaAlertaNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

#[Signature('sistema:verificar-saude')]
#[Description('Verifica disco, memória, jobs de fila falhados e emissões fiscais com erro; alerta por e-mail se algo passar do limite')]
class VerificarSaudeSistema extends Command
{
    private const LIMITE_DISCO_PERCENTUAL = 90;
    private const LIMITE_MEMORIA_PERCENTUAL = 90;

    // Janela igual ao agendamento (hourly, ver routes/console.php): cada
    // execução só olha o que aconteceu desde a última, pra não reenviar o
    // mesmo alerta pra sempre enquanto o problema não for resolvido.
    private const JANELA_HORAS = 1;

    public function handle(): int
    {
        $alertas = array_filter([
            $this->verificarDisco(),
            $this->verificarMemoria(),
            $this->verificarJobsFalhados(),
            $this->verificarEmissoesFiscaisComErro(),
        ]);

        if (empty($alertas)) {
            $this->info('Sistema saudável, nenhum alerta.');

            return self::SUCCESS;
        }

        $destino = config('backup.notifications.mail.to');
        if ($destino) {
            Notification::route('mail', $destino)->notify(new SaudeSistemaAlertaNotification($alertas));
        }

        $this->warn('Alertas encontrados: ' . implode(' | ', $alertas));

        return self::FAILURE;
    }

    private function verificarDisco(): ?string
    {
        $total = @disk_total_space('/');
        $livre = @disk_free_space('/');

        if (! $total || ! $livre) {
            return null;
        }

        $usadoPercentual = round((1 - $livre / $total) * 100, 1);

        if ($usadoPercentual < self::LIMITE_DISCO_PERCENTUAL) {
            return null;
        }

        return "Disco em {$usadoPercentual}% de uso (limite: " . self::LIMITE_DISCO_PERCENTUAL . '%)';
    }

    private function verificarMemoria(): ?string
    {
        if (! is_readable('/proc/meminfo')) {
            return null;
        }

        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+) kB/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+) kB/', $meminfo, $disponivel);

        if (! isset($total[1], $disponivel[1]) || (int) $total[1] === 0) {
            return null;
        }

        $usadoPercentual = round((1 - $disponivel[1] / $total[1]) * 100, 1);

        if ($usadoPercentual < self::LIMITE_MEMORIA_PERCENTUAL) {
            return null;
        }

        return "Memória em {$usadoPercentual}% de uso (limite: " . self::LIMITE_MEMORIA_PERCENTUAL . '%)';
    }

    private function verificarJobsFalhados(): ?string
    {
        $total = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours(self::JANELA_HORAS))
            ->count();

        if ($total === 0) {
            return null;
        }

        return "{$total} job(s) da fila falharam na última hora";
    }

    // Emissão de CT-e/MDF-e roda em fila (EmitirDocumentoFiscalJob) — se a
    // Focus NFe rejeitar ou a chamada falhar, o job em si não "falha" pro
    // Laravel (o erro é tratado e vira status='erro_*' no registro), então
    // não aparece em failed_jobs. Sem esta checagem, uma rejeição fiscal
    // ficaria muda até alguém abrir a tela de emissões por acaso.
    private function verificarEmissoesFiscaisComErro(): ?string
    {
        $total = EmissaoFiscal::whereIn('status', ['erro_autorizacao', 'erro_encerramento', 'erro_cancelamento', 'denegado'])
            ->where('updated_at', '>=', now()->subHours(self::JANELA_HORAS))
            ->count();

        if ($total === 0) {
            return null;
        }

        return "{$total} emissão(ões) fiscal(is) com erro na última hora";
    }
}
