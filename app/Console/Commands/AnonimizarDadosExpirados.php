<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('lgpd:anonimizar {--dry-run : Apenas lista o que seria anonimizado, sem alterar nada}')]
#[Description('Anonimiza dados pessoais de motoristas, clientes (PF) e usuários excluídos há mais tempo que a política de retenção')]
class AnonimizarDadosExpirados extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $totalMotoristas = $this->anonimizarMotoristas($dryRun);
        $totalClientes   = $this->anonimizarClientes($dryRun);
        $totalUsuarios   = $this->anonimizarUsuarios($dryRun);

        $acao = $dryRun ? 'seriam anonimizados' : 'anonimizados';

        $this->info("Motoristas {$acao}: {$totalMotoristas}");
        $this->info("Clientes {$acao}: {$totalClientes}");
        $this->info("Usuários {$acao}: {$totalUsuarios}");

        return self::SUCCESS;
    }

    private function anonimizarMotoristas(bool $dryRun): int
    {
        $limite = now()->subYears((int) config('lgpd.retencao_anos.motoristas'));

        $motoristas = Motorista::onlyTrashed()
            ->whereNull('anonymized_at')
            ->where('deleted_at', '<=', $limite)
            ->get();

        foreach ($motoristas as $motorista) {
            $this->line("  Motorista #{$motorista->id} ({$motorista->nome}) excluído em {$motorista->deleted_at->format('d/m/Y')}");

            if ($dryRun) {
                continue;
            }

            $motorista->nome          = 'Motorista Anonimizado #' . $motorista->id;
            $motorista->cpf           = 'ANON' . $motorista->id;
            $motorista->cnh           = null;
            $motorista->categoria_cnh = null;
            $motorista->validade_cnh  = null;
            $motorista->telefone      = null;
            $motorista->email         = null;
            $motorista->anonymized_at = now();
            $motorista->save();
        }

        return $motoristas->count();
    }

    private function anonimizarClientes(bool $dryRun): int
    {
        $limite = now()->subYears((int) config('lgpd.retencao_anos.clientes'));

        $clientes = Cliente::onlyTrashed()
            ->where('tipo_pessoa', 'fisica')
            ->whereNull('anonymized_at')
            ->where('deleted_at', '<=', $limite)
            ->get();

        foreach ($clientes as $cliente) {
            $this->line("  Cliente #{$cliente->id} ({$cliente->nome}) excluído em {$cliente->deleted_at->format('d/m/Y')}");

            if ($dryRun) {
                continue;
            }

            $cliente->nome        = 'Cliente Anonimizado #' . $cliente->id;
            $cliente->cpf_cnpj    = 'ANON' . $cliente->id;
            $cliente->email       = null;
            $cliente->telefone    = null;
            $cliente->celular     = null;
            $cliente->contato     = null;
            $cliente->cep         = null;
            $cliente->logradouro  = null;
            $cliente->numero      = null;
            $cliente->complemento = null;
            $cliente->bairro      = null;
            $cliente->anonymized_at = now();
            $cliente->save();
        }

        return $clientes->count();
    }

    private function anonimizarUsuarios(bool $dryRun): int
    {
        $limite = now()->subYears((int) config('lgpd.retencao_anos.usuarios'));

        $usuarios = User::onlyTrashed()
            ->whereNull('anonymized_at')
            ->where('deleted_at', '<=', $limite)
            ->get();

        foreach ($usuarios as $usuario) {
            $this->line("  Usuário #{$usuario->id} ({$usuario->name}) excluído em {$usuario->deleted_at->format('d/m/Y')}");

            if ($dryRun) {
                continue;
            }

            $usuario->name          = 'Usuário Anonimizado #' . $usuario->id;
            $usuario->email         = 'anon+' . $usuario->id . '@invexafrete.invalid';
            $usuario->anonymized_at = now();
            $usuario->save();
        }

        return $usuarios->count();
    }
}
