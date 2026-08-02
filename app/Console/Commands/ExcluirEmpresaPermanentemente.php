<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Carga;
use App\Models\Cliente;
use App\Models\DespesaGeral;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\LogAcesso;
use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('empresas:excluir {empresa : ID ou nome exato da empresa} {--dry-run : Apenas lista o que seria apagado, sem alterar nada} {--force : Confirma a exclusão sem perguntar}')]
#[Description('Apaga permanentemente uma empresa e todos os dados vinculados (motoristas, veículos, viagens, clientes, etc.) — irreversível, use para limpar empresas de teste')]
class ExcluirEmpresaPermanentemente extends Command
{
    /**
     * Ordem de exclusão determinada pelas foreign keys `restrictOnDelete()`
     * em empresa_id/viagem_id/motorista_id/veiculo_id/cliente_id — cargas e
     * emissoes_fiscais têm que sumir antes de forceDelete() em viagens
     * (que cascade-apaga lancamentos/descontos/documentos no banco);
     * programacoes_viagem antes de motoristas/veiculos; cargas antes de
     * clientes. Registros já soft-deleted continuam existindo na tabela e
     * bloqueiam a FK igual — por isso withTrashed() em tudo que usa
     * SoftDeletes, com forceDelete() em vez de delete().
     */
    public function handle(): int
    {
        $empresa = is_numeric($this->argument('empresa'))
            ? Empresa::find($this->argument('empresa'))
            : Empresa::where('nome', $this->argument('empresa'))->first();

        if (! $empresa) {
            $this->error("Empresa \"{$this->argument('empresa')}\" não encontrada.");

            return self::FAILURE;
        }

        $id = $empresa->id;

        $contagens = [
            'Usuários'          => User::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Motoristas'        => Motorista::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Veículos'          => Veiculo::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Clientes'          => Cliente::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Viagens'           => Viagem::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Cargas'            => Carga::withoutGlobalScope('empresa')->where('empresa_id', $id)->count(),
            'Emissões fiscais'  => EmissaoFiscal::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Despesas gerais'   => DespesaGeral::withoutGlobalScope('empresa')->where('empresa_id', $id)->count(),
            'Programações'      => ProgramacaoViagem::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->count(),
            'Logs de acesso'    => LogAcesso::withoutGlobalScope('empresa')->where('empresa_id', $id)->count(),
        ];

        $this->info("Empresa #{$id} — {$empresa->nome}");
        $this->table(['O que será apagado', 'Registros'], collect($contagens)->map(fn ($v, $k) => [$k, $v])->values());

        if ($this->option('dry-run')) {
            $this->comment('Modo dry-run: nada foi apagado.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn('Esta ação é IRREVERSÍVEL. Todos os registros acima serão apagados permanentemente.');
            $confirmacao = $this->ask('Digite o nome exato da empresa para confirmar');

            if ($confirmacao !== $empresa->nome) {
                $this->error('Nome não confere. Operação cancelada.');

                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($id, $empresa) {
            Carga::withoutGlobalScope('empresa')->where('empresa_id', $id)->delete();
            EmissaoFiscal::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($e) => $e->forceDelete());
            ProgramacaoViagem::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($p) => $p->forceDelete());
            Viagem::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($v) => $v->forceDelete());
            DespesaGeral::withoutGlobalScope('empresa')->where('empresa_id', $id)->delete();
            Motorista::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($m) => $m->forceDelete());
            Veiculo::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($v) => $v->forceDelete());
            Cliente::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($c) => $c->forceDelete());
            LogAcesso::withoutGlobalScope('empresa')->where('empresa_id', $id)->delete();
            ActivityLog::where('empresa_id', $id)->delete();
            User::withoutGlobalScope('empresa')->withTrashed()->where('empresa_id', $id)->each(fn ($u) => $u->forceDelete());

            $nome = $empresa->nome;
            $empresa->delete();

            Log::warning("Empresa #{$id} ({$nome}) excluída permanentemente via comando empresas:excluir.");
        });

        $this->info("Empresa \"{$empresa->nome}\" e todos os dados vinculados foram apagados.");

        return self::SUCCESS;
    }
}
