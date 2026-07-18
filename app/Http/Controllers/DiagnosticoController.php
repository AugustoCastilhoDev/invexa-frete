<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Support\Facades\DB;

class DiagnosticoController extends Controller
{
    public function index()
    {
        return view('diagnostico.index', [
            'servidor' => $this->metricasServidor(),
            'aplicacao' => $this->metricasAplicacao(),
        ]);
    }

    private function metricasServidor(): array
    {
        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

        $memTotal = null;
        $memDisponivel = null;
        if (is_readable('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+) kB/', $meminfo, $mTotal);
            preg_match('/MemAvailable:\s+(\d+) kB/', $meminfo, $mDisponivel);
            $memTotal = isset($mTotal[1]) ? ((int) $mTotal[1]) * 1024 : null;
            $memDisponivel = isset($mDisponivel[1]) ? ((int) $mDisponivel[1]) * 1024 : null;
        }

        $uptimeSegundos = null;
        if (is_readable('/proc/uptime')) {
            $uptimeSegundos = (int) floatval(explode(' ', trim(file_get_contents('/proc/uptime')))[0]);
        }

        return [
            'load' => $load,
            'mem_total' => $memTotal,
            'mem_disponivel' => $memDisponivel,
            'mem_usada_percentual' => ($memTotal && $memDisponivel)
                ? round((1 - $memDisponivel / $memTotal) * 100, 1)
                : null,
            'disco_total' => @disk_total_space('/') ?: null,
            'disco_livre' => @disk_free_space('/') ?: null,
            'uptime_segundos' => $uptimeSegundos,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'ambiente' => app()->environment(),
        ];
    }

    private function metricasAplicacao(): array
    {
        // information_schema.tables é sintaxe específica do MySQL — em testes
        // (SQLite em memória) essa consulta não existe, então só roda no driver real.
        $tamanhoBanco = 0;
        if (DB::connection()->getDriverName() === 'mysql') {
            $tamanhoBanco = DB::selectOne(
                'SELECT SUM(data_length + index_length) AS bytes FROM information_schema.tables WHERE table_schema = DATABASE()'
            )->bytes ?? 0;
        }

        return [
            'usuarios_online' => DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                ->distinct()
                ->count('user_id'),
            'usuarios_ativos_24h' => DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subDay()->timestamp)
                ->distinct()
                ->count('user_id'),
            'empresas_ativas' => Empresa::where('status', 'ativo')->count(),
            'empresas_total' => Empresa::count(),
            'usuarios_total' => User::count(),
            'veiculos_total' => Veiculo::withoutGlobalScope('empresa')->count(),
            'motoristas_total' => Motorista::withoutGlobalScope('empresa')->count(),
            'clientes_total' => Cliente::withoutGlobalScope('empresa')->count(),
            'viagens_total' => Viagem::withoutGlobalScope('empresa')->count(),
            'tamanho_banco_bytes' => (int) $tamanhoBanco,
        ];
    }
}
