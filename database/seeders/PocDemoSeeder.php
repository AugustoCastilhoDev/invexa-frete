<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DespesaGeral;
use App\Models\Empresa;
use App\Models\Manutencao;
use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

/**
 * Massa de dados 100% fictícia (nomes/CPF/CNPJ gerados por faker, nenhum
 * dado real) pra popular o ambiente de homologação antes de uma demo ou da
 * POC em sombra — atende ao item A3 do Gate A da Devolutiva Executiva de
 * Prontidão. Cobre o fluxo ponta a ponta (evidência E1): cadastro,
 * programação de frota, viagem em cada status do ciclo, motorista com
 * acesso ao Portal, manutenção e despesa geral pra alimentar DRE/Custo da
 * Frota. Nunca roda em produção (guarda de ambiente abaixo).
 *
 * Uso: php artisan db:seed --class=PocDemoSeeder
 */
class PocDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('PocDemoSeeder não roda em produção — use só em homologação.');

            return;
        }

        $empresa = Empresa::factory()->create([
            'nome' => 'Transportes Horizonte Demo (POC)',
            'status' => 'ativo',
        ]);

        TenantContext::forceId($empresa->id);

        // semDoisFatores() de propósito: reflete a conta de um admin recém-criado
        // de verdade, que configura 2FA no primeiro login (EnsureTwoFactorIsEnabled)
        // — dá pra usar isso como parte da própria demonstração (evidência E1),
        // em vez de mascarar o comportamento real com um 2FA pré-confirmado.
        User::factory()->admin()->semDoisFatores()->create([
            'name' => 'Admin Demo POC',
            'email' => 'admin.poc@invexafrete-demo.com.br',
        ]);

        $motoristas = Motorista::factory()->count(4)->create();
        $motoristaComPortal = Motorista::factory()->comAcessoPortal('senha-demo')->create([
            'nome' => 'Motorista Demo (Portal)',
            'cpf' => '000.111.222-33',
        ]);

        $cavalo = Veiculo::factory()->create(['tipo' => 'truck']);
        Veiculo::factory()->vinculadaA($cavalo)->create();
        $veiculos = Veiculo::factory()->count(3)->create();

        $clientes = Cliente::factory()->count(3)->create();

        // Viagens em cada status do ciclo, pro fluxo ponta a ponta (E1)
        Viagem::factory()->create([
            'motorista_id' => $motoristas[0]->id,
            'veiculo_id' => $veiculos[0]->id,
            'cliente_id' => $clientes[0]->id,
            'status' => 'aberta',
        ]);
        Viagem::factory()->create([
            'motorista_id' => $motoristas[1]->id,
            'veiculo_id' => $veiculos[1]->id,
            'cliente_id' => $clientes[1]->id,
            'status' => 'em_andamento',
        ]);
        Viagem::factory()->aguardandoAcerto()->create([
            'motorista_id' => $motoristaComPortal->id,
            'veiculo_id' => $veiculos[2]->id,
            'cliente_id' => $clientes[2]->id,
        ]);
        Viagem::factory()->encerrada()->count(3)->create([
            'motorista_id' => $motoristas[2]->id,
            'veiculo_id' => $cavalo->id,
            'cliente_id' => $clientes[0]->id,
        ]);

        // Programação de Frota (próxima viagem planejada)
        ProgramacaoViagem::factory()->confirmada()->create([
            'motorista_id' => $motoristas[3]->id,
            'veiculo_id' => $veiculos[0]->id,
        ]);

        // Manutenção e Despesa Geral, pra alimentar Custo da Frota / DRE
        Manutencao::factory()->create(['veiculo_id' => $veiculos[0]->id]);
        DespesaGeral::factory()->count(3)->create();

        $this->command?->info("Empresa demo criada: #{$empresa->id} — {$empresa->nome} (admin: admin.poc@invexafrete-demo.com.br / password)");
    }
}
