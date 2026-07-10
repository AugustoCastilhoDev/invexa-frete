<?php

namespace App\Services\FocusNfe;

use App\Models\Empresa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FocusNfeClient
{
    public function baseUrl(string $ambiente): string
    {
        // TODO: confirmar o hostname exato de produção contra doc.focusnfe.com.br
        // antes da primeira ativação real — não usado enquanto nenhuma empresa
        // estiver em ambiente "producao".
        return $ambiente === 'producao'
            ? 'https://api.focusnfe.com.br'
            : 'https://homologacao.focusnfe.com.br';
    }

    public function configurado(): bool
    {
        return filled(config('services.focus_nfe.token_conta_principal'));
    }

    /**
     * Registra a empresa-cliente como sub-empresa da conta principal da Invexa
     * na Focus NFe. O schema exato do body (nomes dos campos de CNPJ/certificado/
     * senha) ainda não foi confirmado contra a referência ao vivo — não travar
     * esses nomes sem antes rodar uma chamada real em homologação.
     */
    public function registrarEmpresa(array $dadosEmpresa, string $certificadoBase64, string $certificadoSenha, string $ambiente): ?array
    {
        if (! $this->configurado()) {
            Log::warning('Focus NFe: tentativa de registrar empresa sem FOCUS_NFE_TOKEN_CONTA_PRINCIPAL configurado.');

            return null;
        }

        try {
            $response = $this->httpContaPrincipal($ambiente)->post('/v2/empresas', [
                'cnpj' => $dadosEmpresa['cnpj'] ?? null,
                'nome' => $dadosEmpresa['nome'] ?? null,
                'arquivo_certificado_base64' => $certificadoBase64,
                'senha_certificado' => $certificadoSenha,
            ]);
        } catch (\Throwable $e) {
            Log::error('Focus NFe: falha de transporte ao registrar empresa.', ['erro' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Focus NFe: falha ao registrar empresa.', ['status' => $response->status(), 'body' => $response->json()]);

            return null;
        }

        return $response->json();
    }

    public function emitirCte(Empresa $empresa, string $referencia, array $payload): ?array
    {
        return $this->enviarDocumento($empresa, '/v2/cte', $referencia, $payload);
    }

    public function emitirMdfe(Empresa $empresa, string $referencia, array $payload): ?array
    {
        return $this->enviarDocumento($empresa, '/v2/mdfe', $referencia, $payload);
    }

    public function consultarCte(Empresa $empresa, string $referencia): ?array
    {
        return $this->consultar($empresa, "/v2/cte/{$referencia}");
    }

    public function consultarMdfe(Empresa $empresa, string $referencia): ?array
    {
        return $this->consultar($empresa, "/v2/mdfe/{$referencia}");
    }

    /**
     * Retorna null apenas quando nem tentamos a chamada (empresa não ativa /
     * sem token / falha de transporte). Um erro de negócio real da Focus
     * (400/409/422) ainda retorna o body decodificado — é o que carrega
     * "codigo"/"mensagem" que EmissaoFiscal::aplicarRespostaFocus() precisa
     * persistir para o usuário entender o que deu errado.
     */
    private function enviarDocumento(Empresa $empresa, string $path, string $referencia, array $payload): ?array
    {
        if (! $empresa->focus_nfe_ativo || ! filled($empresa->focus_nfe_token)) {
            Log::warning("Focus NFe: tentativa de emitir sem a empresa #{$empresa->id} estar ativa/configurada.");

            return null;
        }

        try {
            $response = $this->http($empresa)->post($path, $payload, ['ref' => $referencia]);
        } catch (\Throwable $e) {
            Log::error('Focus NFe: falha de transporte ao emitir documento.', ['erro' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful() && $response->status() !== 202) {
            Log::warning('Focus NFe: resposta de erro ao emitir documento.', ['status' => $response->status(), 'body' => $response->json()]);
        }

        return $response->json();
    }

    private function consultar(Empresa $empresa, string $path): ?array
    {
        if (! $empresa->focus_nfe_ativo || ! filled($empresa->focus_nfe_token)) {
            return null;
        }

        try {
            $response = $this->http($empresa)->get($path);
        } catch (\Throwable $e) {
            Log::error('Focus NFe: falha de transporte ao consultar documento.', ['erro' => $e->getMessage()]);

            return null;
        }

        return $response->json();
    }

    private function http(Empresa $empresa)
    {
        return Http::baseUrl($this->baseUrl($empresa->focus_nfe_ambiente))
            ->withBasicAuth($empresa->focus_nfe_token, '')
            ->acceptJson();
    }

    private function httpContaPrincipal(string $ambiente)
    {
        return Http::baseUrl($this->baseUrl($ambiente))
            ->withBasicAuth(config('services.focus_nfe.token_conta_principal'), '')
            ->acceptJson();
    }
}
