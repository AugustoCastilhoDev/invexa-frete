<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CsvImporter
{
    /**
     * Lê um CSV (aceita ; ou , como separador, com ou sem BOM) e, para cada
     * linha, valida com as regras informadas e chama $criar se for válida.
     * Linhas inválidas não abortam o processo — ficam registradas em "erros"
     * com o número da linha e o motivo, para o usuário corrigir e reenviar.
     *
     * @param  array<string,string>  $colunas  Mapa "cabeçalho esperado no CSV" => "campo do sistema"
     * @param  array<string,mixed>  $regras  Regras de validação (Laravel Validator) por campo do sistema
     * @param  callable(array<string,mixed>):void  $criar
     * @return array{importados: int, erros: array<int, array{linha:int, mensagens: array<int,string>}>}
     */
    public static function importar(UploadedFile $arquivo, array $colunas, array $regras, callable $criar): array
    {
        // Planilhas grandes (centenas de linhas) podem passar dos 30s padrão do
        // PHP — cada linha faz sua própria validação "unique" (consulta ao banco).
        // Sem isso, o processo morre no meio e a exceção nem chega a ser
        // registrada como erro de linha.
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $conteudo = file_get_contents($arquivo->getRealPath());
        $conteudo = preg_replace('/^\x{FEFF}/u', '', $conteudo);

        $delimitador = substr_count(strtok($conteudo, "\n"), ';') > substr_count(strtok($conteudo, "\n"), ',') ? ';' : ',';

        $linhas = array_filter(preg_split('/\r\n|\r|\n/', $conteudo), fn ($l) => trim($l) !== '');
        $linhas = array_values($linhas);

        if (empty($linhas)) {
            return ['importados' => 0, 'erros' => []];
        }

        $cabecalho = array_map(fn ($c) => mb_strtolower(trim($c)), str_getcsv(array_shift($linhas), $delimitador));

        $indices = [];
        foreach ($colunas as $cabecalhoCsv => $campoSistema) {
            $posicao = array_search(mb_strtolower($cabecalhoCsv), $cabecalho, true);
            if ($posicao !== false) {
                $indices[$campoSistema] = $posicao;
            }
        }

        $importados = 0;
        $erros = [];

        // Tudo numa única transação: se o processo cair no meio (timeout,
        // conexão derrubada etc.), o MySQL desfaz sozinho ao fechar a conexão
        // em vez de deixar só uma parte da planilha gravada no banco.
        DB::transaction(function () use ($linhas, $indices, $regras, $delimitador, $criar, &$importados, &$erros) {
            foreach ($linhas as $i => $linha) {
                $valores = str_getcsv($linha, $delimitador);

                $dados = [];
                foreach ($indices as $campoSistema => $posicao) {
                    $dados[$campoSistema] = isset($valores[$posicao]) ? trim($valores[$posicao]) : null;
                }
                $dados = array_map(function ($v) {
                    if ($v === '') {
                        return null;
                    }

                    // Datas no formato brasileiro (dd/mm/aaaa) não são reconhecidas
                    // pela regra "date" do Laravel — normaliza para aaaa-mm-dd antes de validar.
                    if (is_string($v) && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $v, $m)) {
                        return "{$m[3]}-{$m[2]}-{$m[1]}";
                    }

                    return $v;
                }, $dados);

                $validator = Validator::make($dados, $regras);

                if ($validator->fails()) {
                    $erros[] = ['linha' => $i + 2, 'mensagens' => $validator->errors()->all()];

                    continue;
                }

                try {
                    $criar($validator->validated());
                    $importados++;
                } catch (\RuntimeException $e) {
                    $erros[] = ['linha' => $i + 2, 'mensagens' => [$e->getMessage()]];
                }
            }
        });

        return ['importados' => $importados, 'erros' => $erros];
    }
}
