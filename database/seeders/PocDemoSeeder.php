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
use Illuminate\Support\Facades\Hash;

/**
 * Massa de dados 100% fictícia pra popular o ambiente de homologação antes
 * de uma demo ou da POC em sombra — atende ao item A3 do Gate A da
 * Devolutiva Executiva de Prontidão. Cobre o fluxo ponta a ponta (evidência
 * E1): cadastro, programação de frota, viagem em cada status do ciclo,
 * motorista com acesso ao Portal, manutenção e despesa geral pra alimentar
 * DRE/Custo da Frota. Nunca roda em produção (guarda de ambiente abaixo).
 *
 * Deliberadamente sem `::factory()`/Faker: fakerphp/faker é dependência de
 * desenvolvimento (composer.json `require-dev`), ausente depois de um
 * `composer install --no-dev` — exatamente como produção e homologação são
 * deployadas (`deploy/deploy.sh`). Dado fictício escrito na mão evita
 * precisar mover Faker pra `require` só por causa de uma ferramenta de demo.
 *
 * Uso: php artisan db:seed --class=PocDemoSeeder
 */
class PocDemoSeeder extends Seeder
{
    private const NOMES = [
        'Carlos Eduardo Silva', 'Marcos Vinícius Oliveira', 'José Roberto Santos',
        'Antônio Carlos Pereira', 'Francisco das Chagas Lima', 'Paulo Henrique Souza',
    ];

    private const CIDADES = [
        ['Curitiba', 'PR'], ['São Paulo', 'SP'], ['Joinville', 'SC'],
        ['Londrina', 'PR'], ['Maringá', 'PR'], ['Cascavel', 'PR'],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('PocDemoSeeder não roda em produção — use só em homologação.');

            return;
        }

        $empresa = Empresa::create([
            'nome' => 'Transportes Horizonte Demo (POC)',
            'cnpj' => '12.345.678/0001-90',
            'status' => 'ativo',
        ]);

        TenantContext::forceId($empresa->id);

        // semDoisFatores de propósito: reflete a conta de um admin recém-criado
        // de verdade, que configura 2FA no primeiro login (EnsureTwoFactorIsEnabled)
        // — dá pra usar isso como parte da própria demonstração (evidência E1),
        // em vez de mascarar o comportamento real com um 2FA pré-confirmado.
        User::create([
            'name' => 'Admin Demo POC',
            'email' => 'admin.poc@invexafrete-demo.com.br',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'ativo',
            'email_verified_at' => now(),
        ]);

        $motoristas = [];
        foreach (self::NOMES as $i => $nome) {
            $motoristas[] = Motorista::create([
                'nome' => $nome,
                'cpf' => sprintf('%03d.%03d.%03d-%02d', 100 + $i, 200 + $i, 300 + $i, 10 + $i),
                'cnh' => sprintf('%011d', 90000000000 + $i),
                'categoria_cnh' => 'E',
                'validade_cnh' => now()->addYears(2)->format('Y-m-d'),
                'telefone' => sprintf('(41) 9%04d-%04d', 1000 + $i, 2000 + $i),
                'percentual_comissao' => 10,
                'status' => 'ativo',
                // Alterna propria/agregado pra já mostrar os dois na demo
                'vinculo' => $i % 2 === 0 ? 'propria' : 'agregado',
            ]);
        }
        $motoristaComPortal = Motorista::create([
            'nome' => 'Motorista Demo (Portal)',
            'cpf' => '000.111.222-33',
            'cnh' => '00099988877',
            'categoria_cnh' => 'E',
            'validade_cnh' => now()->addYears(2)->format('Y-m-d'),
            'telefone' => '(41) 90000-0000',
            'percentual_comissao' => 10,
            'status' => 'ativo',
            'password' => Hash::make('senha-demo'),
            'portal_ativo' => true,
        ]);

        $cavalo = Veiculo::create([
            'placa' => 'POC1A11', 'modelo' => 'FH 540', 'marca' => 'Volvo', 'ano' => 2022,
            'tipo' => 'cavalo_trucado', 'renavam' => '10000000001', 'capacidade_kg' => 15000, 'status' => 'ativo',
        ]);
        Veiculo::create([
            'placa' => 'POC1B22', 'modelo' => 'Graneleiro', 'marca' => 'Randon', 'ano' => 2021,
            'tipo' => 'carreta', 'tipo_carroceria' => 'graneleiro', 'cavalo_id' => $cavalo->id, 'renavam' => '10000000002',
            'capacidade_kg' => 30000, 'status' => 'ativo',
        ]);
        $veiculosDados = [
            ['POC2C33', 'Actros', 'Mercedes-Benz', 'truck'],
            ['POC2D44', 'Constellation', 'Volkswagen', 'truck'],
            ['POC2E55', 'Delivery', 'Volkswagen', 'van'],
        ];
        $veiculos = [];
        foreach ($veiculosDados as $i => [$placa, $modelo, $marca, $tipo]) {
            $veiculos[] = Veiculo::create([
                'placa' => $placa, 'modelo' => $modelo, 'marca' => $marca, 'ano' => 2020 + $i,
                'tipo' => $tipo, 'renavam' => sprintf('1000000%04d', $i), 'capacidade_kg' => 10000,
                'status' => 'ativo',
                // Alterna propria/agregado pra já mostrar os dois na demo
                'vinculo' => $i % 2 === 0 ? 'propria' : 'agregado',
            ]);
        }

        $clientes = [];
        foreach (self::CIDADES as $i => [$cidade, $uf]) {
            $clientes[] = Cliente::create([
                'tipo_pessoa' => 'juridica',
                'nome' => "Comércio Demo {$cidade} Ltda",
                'razao_social' => "Comércio Demo {$cidade} Ltda",
                'cpf_cnpj' => sprintf('%02d.%03d.%03d/0001-%02d', 20 + $i, 100 + $i, 200 + $i, 30 + $i),
                'cidade' => $cidade,
                'estado' => $uf,
                'tabela_frete' => 3.5,
                'status' => 'ativo',
            ]);
            if ($i >= 2) {
                break;
            }
        }

        // Viagens em cada status do ciclo, pro fluxo ponta a ponta (E1)
        $baseViagem = [
            'origem' => 'Curitiba', 'destino' => 'São Paulo',
            'valor_frete' => 3500, 'percentual_motorista' => 10, 'valor_motorista' => 350,
            'saldo_motorista' => 350, 'lucro_transportadora' => 3150,
        ];

        Viagem::create($baseViagem + [
            'motorista_id' => $motoristas[0]->id, 'veiculo_id' => $veiculos[0]->id, 'cliente_id' => $clientes[0]->id,
            'data_saida' => now()->format('Y-m-d'), 'km_inicial' => 1000, 'status' => 'aberta',
        ]);
        Viagem::create($baseViagem + [
            'motorista_id' => $motoristas[1]->id, 'veiculo_id' => $veiculos[1]->id, 'cliente_id' => $clientes[1]->id,
            'data_saida' => now()->subDay()->format('Y-m-d'), 'km_inicial' => 2000, 'status' => 'em_andamento',
        ]);
        // Ancoradas no início do mês corrente (não em "hoje menos N dias"): assim
        // continuam caindo dentro do período padrão dos relatórios (DRE, Relatório
        // Financeiro, Custo da Frota — todos filtram "mês atual" por padrão) não
        // importa em que dia do mês o seeder for rodado.
        $inicioMes = now()->startOfMonth();

        Viagem::create($baseViagem + [
            'motorista_id' => $motoristaComPortal->id, 'veiculo_id' => $veiculos[2]->id, 'cliente_id' => $clientes[2]->id,
            'data_saida' => $inicioMes->copy()->format('Y-m-d'), 'km_inicial' => 3000, 'km_final' => 3620,
            'status' => 'aguardando_acerto',
        ]);
        for ($i = 0; $i < 3; $i++) {
            Viagem::create($baseViagem + [
                'motorista_id' => $motoristas[2]->id, 'veiculo_id' => $cavalo->id, 'cliente_id' => $clientes[0]->id,
                'data_saida' => $inicioMes->copy()->addDays($i)->format('Y-m-d'),
                'data_retorno' => $inicioMes->copy()->addDays($i + 1),
                'km_inicial' => 4000 + ($i * 1000), 'km_final' => 4600 + ($i * 1000),
                'status' => 'encerrada',
            ]);
        }

        // Programação de Frota (próxima viagem planejada)
        ProgramacaoViagem::create([
            'motorista_id' => $motoristas[3]->id, 'veiculo_id' => $veiculos[0]->id,
            'origem' => 'Curitiba', 'destino' => 'Joinville',
            'data_prevista' => now()->addDays(3)->format('Y-m-d'), 'status' => 'confirmada',
        ]);

        // Manutenção e Despesa Geral, pra alimentar Custo da Frota / DRE
        Manutencao::create([
            'veiculo_id' => $veiculos[0]->id, 'tipo' => 'preventiva', 'descricao' => 'Troca de óleo e filtros',
            'data_manutencao' => $inicioMes->copy()->format('Y-m-d'), 'km_veiculo' => 45000, 'valor' => 850,
            'status' => 'concluida',
        ]);
        $despesas = [
            ['aluguel', 'Aluguel do pátio', 2500],
            ['salarios', 'Folha administrativa', 4200],
            ['seguro', 'Seguro da frota', 1300],
        ];
        foreach ($despesas as [$categoria, $descricao, $valor]) {
            DespesaGeral::create([
                'categoria' => $categoria, 'descricao' => $descricao, 'valor' => $valor,
                'data_despesa' => $inicioMes->copy()->format('Y-m-d'), 'recorrente' => true,
            ]);
        }

        $this->command?->info("Empresa demo criada: #{$empresa->id} — {$empresa->nome} (admin: admin.poc@invexafrete-demo.com.br / password)");
    }
}
