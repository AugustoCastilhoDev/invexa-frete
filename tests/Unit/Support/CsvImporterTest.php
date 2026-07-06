<?php

namespace Tests\Unit\Support;

use App\Support\CsvImporter;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImporterTest extends TestCase
{
    private function arquivo(string $conteudo): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('dados.csv', $conteudo);
    }

    public function test_importa_linhas_validas_com_separador_ponto_e_virgula(): void
    {
        $csv = "nome;idade\nJoão;30\nMaria;25\n";
        $criados = [];

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'idade' => 'idade'],
            ['nome' => 'required|string', 'idade' => 'required|integer'],
            function (array $dados) use (&$criados) { $criados[] = $dados; }
        );

        $this->assertSame(2, $resultado['importados']);
        $this->assertEmpty($resultado['erros']);
        $this->assertCount(2, $criados);
        $this->assertSame('João', $criados[0]['nome']);
    }

    public function test_importa_com_separador_virgula(): void
    {
        $csv = "nome,idade\nJoão,30\n";

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'idade' => 'idade'],
            ['nome' => 'required|string', 'idade' => 'required|integer'],
            fn (array $dados) => null
        );

        $this->assertSame(1, $resultado['importados']);
    }

    public function test_remove_bom_utf8_do_inicio_do_arquivo(): void
    {
        $csv = "\xEF\xBB\xBFnome;idade\nJoão;30\n";

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'idade' => 'idade'],
            ['nome' => 'required|string', 'idade' => 'required|integer'],
            fn (array $dados) => null
        );

        $this->assertSame(1, $resultado['importados']);
    }

    public function test_linha_invalida_e_registrada_como_erro_sem_interromper_as_demais(): void
    {
        $csv = "nome;idade\n;30\nMaria;25\n";

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'idade' => 'idade'],
            ['nome' => 'required|string', 'idade' => 'required|integer'],
            fn (array $dados) => null
        );

        $this->assertSame(1, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
        $this->assertSame(2, $resultado['erros'][0]['linha']);
    }

    public function test_excecao_de_execucao_na_criacao_vira_erro_da_linha(): void
    {
        $csv = "nome;idade\nJoão;30\n";

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'idade' => 'idade'],
            ['nome' => 'required|string', 'idade' => 'required|integer'],
            function (array $dados) { throw new \RuntimeException('limite atingido'); }
        );

        $this->assertSame(0, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
        $this->assertSame(['limite atingido'], $resultado['erros'][0]['mensagens']);
    }

    public function test_normaliza_data_no_formato_brasileiro_antes_de_validar(): void
    {
        $csv = "nome;nascimento\nJoão;31/12/2029\n";
        $criados = [];

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'nascimento' => 'nascimento'],
            ['nome' => 'required|string', 'nascimento' => 'required|date'],
            function (array $dados) use (&$criados) { $criados[] = $dados; }
        );

        $this->assertSame(1, $resultado['importados']);
        $this->assertEmpty($resultado['erros']);
        $this->assertSame('2029-12-31', $criados[0]['nascimento']);
    }

    public function test_ignora_colunas_do_csv_que_nao_estao_mapeadas(): void
    {
        $csv = "nome;idade;coluna_extra\nJoão;30;qualquer coisa\n";

        $resultado = CsvImporter::importar(
            $this->arquivo($csv),
            ['nome' => 'nome', 'idade' => 'idade'],
            ['nome' => 'required|string', 'idade' => 'required|integer'],
            fn (array $dados) => null
        );

        $this->assertSame(1, $resultado['importados']);
    }
}
