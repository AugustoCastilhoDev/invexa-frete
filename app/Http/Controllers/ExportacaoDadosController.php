<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Desconto;
use App\Models\DespesaGeral;
use App\Models\Documento;
use App\Models\EmissaoFiscal;
use App\Models\Lancamento;
use App\Models\Manutencao;
use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\TabelaFrete;
use App\Models\Veiculo;
use App\Models\Viagem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ExportacaoDadosController extends Controller
{
    public function index()
    {
        return view('exportacao-dados.index');
    }

    // Portabilidade de dados (LGPD art. 18) e saída sem aprisionamento: gera
    // um ZIP com todo o dado estruturado da empresa em CSV, pronto pra abrir
    // em qualquer planilha ou importar em outro sistema.
    public function exportar(): BinaryFileResponse
    {
        $caminho = tempnam(sys_get_temp_dir(), 'invexa_export_') . '.zip';

        $zip = new ZipArchive();
        $zip->open($caminho, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('clientes.csv', $this->csv(
            Cliente::all(),
            ['id', 'tipo_pessoa', 'nome', 'razao_social', 'cpf_cnpj', 'email', 'telefone', 'celular', 'cidade', 'estado', 'status', 'created_at']
        ));

        $zip->addFromString('motoristas.csv', $this->csv(
            Motorista::all(),
            ['id', 'nome', 'cpf', 'cnh', 'categoria_cnh', 'validade_cnh', 'telefone', 'email', 'percentual_comissao', 'status', 'created_at']
        ));

        $zip->addFromString('veiculos.csv', $this->csv(
            Veiculo::all(),
            ['id', 'placa', 'modelo', 'marca', 'ano', 'tipo', 'renavam', 'chassi', 'cavalo_id', 'capacidade_kg', 'tara_kg', 'status', 'created_at']
        ));

        $zip->addFromString('viagens.csv', $this->csv(
            Viagem::all(),
            ['id', 'motorista_id', 'veiculo_id', 'cliente_id', 'origem', 'destino', 'data_saida', 'data_retorno', 'valor_frete', 'valor_motorista', 'saldo_motorista', 'status', 'created_at']
        ));

        $zip->addFromString('lancamentos.csv', $this->csv(
            Lancamento::all(),
            ['id', 'viagem_id', 'tipo', 'descricao', 'valor', 'litros', 'valor_litro', 'data_lancamento', 'status', 'created_at']
        ));

        $zip->addFromString('descontos.csv', $this->csv(
            Desconto::all(),
            ['id', 'viagem_id', 'tipo', 'descricao', 'valor', 'data_desconto', 'created_at']
        ));

        $zip->addFromString('documentos.csv', $this->csv(
            Documento::all(),
            ['id', 'viagem_id', 'tipo', 'numero', 'chave_acesso', 'serie', 'data_emissao', 'valor', 'status', 'created_at']
        ));

        $zip->addFromString('emissoes_fiscais.csv', $this->csv(
            EmissaoFiscal::all(),
            ['id', 'viagem_id', 'tipo', 'referencia', 'status', 'chave_acesso', 'numero', 'serie', 'autorizado_em', 'encerrado_em', 'cancelado_em']
        ));

        $zip->addFromString('manutencoes.csv', $this->csv(
            Manutencao::all(),
            ['id', 'veiculo_id', 'tipo', 'descricao', 'data_manutencao', 'km_veiculo', 'valor', 'status', 'created_at']
        ));

        $zip->addFromString('despesas_gerais.csv', $this->csv(
            DespesaGeral::all(),
            ['id', 'categoria', 'descricao', 'valor', 'data_despesa', 'recorrente', 'created_at']
        ));

        $zip->addFromString('tabelas_frete.csv', $this->csv(
            TabelaFrete::all(),
            ['id', 'cliente_id', 'origem', 'origem_uf', 'destino', 'destino_uf', 'valor_frete', 'created_at']
        ));

        $zip->addFromString('programacoes_viagem.csv', $this->csv(
            ProgramacaoViagem::all(),
            ['id', 'motorista_id', 'veiculo_id', 'cliente_id', 'origem', 'destino', 'valor_frete', 'data_prevista', 'status', 'created_at']
        ));

        $zip->close();

        $nomeArquivo = 'invexa-frete-dados-' . now()->format('Y-m-d') . '.zip';

        return response()->download($caminho, $nomeArquivo)->deleteFileAfterSend(true);
    }

    private function csv(iterable $linhas, array $colunas): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $colunas);

        foreach ($linhas as $linha) {
            fputcsv($handle, array_map(fn ($coluna) => $linha->getAttribute($coluna), $colunas));
        }

        rewind($handle);
        $conteudo = stream_get_contents($handle);
        fclose($handle);

        return $conteudo;
    }
}
