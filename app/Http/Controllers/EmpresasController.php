<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DespesaGeral;
use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Services\Asaas\AsaasClient;
use App\Services\Asaas\PlanoPricing;
use App\Services\FocusNfe\FocusNfeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmpresasController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');

        $empresas = Empresa::when($busca, function ($query) use ($busca) {
                $query->where('nome', 'like', "%{$busca}%")
                    ->orWhere('cnpj', 'like', "%{$busca}%");
            })
            ->withCount('usuarios')
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('empresas.index', compact('empresas', 'busca'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request, AsaasClient $asaas)
    {
        $request->validate([
            'nome'                 => 'required|string|max:255',
            'cnpj'                 => 'nullable|string|max:20|unique:empresas,cnpj',
            'limite_veiculos'      => 'nullable|integer|min:1',
            'plano'                => 'required|in:starter,pro,business,enterprise',
            'ciclo_cobranca'       => 'required_unless:plano,enterprise|in:mensal,anual',
            'admin_name'           => 'required|string|max:255',
            'admin_email'          => 'required|email|unique:users,email',
            'admin_password'       => ['required', 'confirmed', Password::defaults()],
        ]);

        $empresa = Empresa::create([
            'nome'            => $request->nome,
            'cnpj'            => $request->cnpj,
            'limite_veiculos' => $request->limite_veiculos,
            'plano'           => $request->plano,
            'ciclo_cobranca'  => $request->plano === 'enterprise' ? null : $request->ciclo_cobranca,
            'status'          => 'ativo',
        ]);

        $admin = new User([
            'name'     => $request->admin_name,
            'email'    => $request->admin_email,
            'password' => $request->admin_password,
            'role'     => 'admin',
            'status'   => 'ativo',
        ]);
        $admin->empresa_id = $empresa->id;
        $admin->email_verified_at = now();
        $admin->save();

        if ($request->plano !== 'enterprise') {
            $this->criarAssinaturaAsaas($asaas, $empresa, $admin, $request->plano, $request->ciclo_cobranca);
        }

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa cadastrada com sucesso! O administrador já pode fazer login.');
    }

    /**
     * Cria o cliente e a assinatura recorrente no Asaas para o plano escolhido,
     * com 14 dias de trial (primeira cobrança só depois desse prazo). Falhas
     * aqui não impedem o cadastro da empresa — só ficam sem o vínculo de
     * cobrança até serem resolvidas manualmente (ex.: chave da API ausente).
     */
    private function criarAssinaturaAsaas(AsaasClient $asaas, Empresa $empresa, User $admin, string $plano, string $ciclo): void
    {
        $customerId = $asaas->criarCliente([
            'nome' => $empresa->nome,
            'email' => $admin->email,
            'cpf_cnpj' => $empresa->cnpj,
        ]);

        if (! $customerId) {
            return;
        }

        $subscriptionId = $asaas->criarAssinatura($customerId, [
            'valor' => PlanoPricing::valor($plano, $ciclo),
            'ciclo' => $ciclo === 'anual' ? 'YEARLY' : 'MONTHLY',
            'proxima_cobranca' => now()->addDays(14)->format('Y-m-d'),
            'descricao' => "Invexa Frete — Plano " . ucfirst($plano),
        ]);

        $empresa->update([
            'asaas_customer_id' => $customerId,
            'asaas_subscription_id' => $subscriptionId,
            'asaas_status' => $subscriptionId ? 'em_trial' : null,
        ]);
    }

    /**
     * Cria (ou recria) a assinatura no Asaas para uma empresa já cadastrada
     * sem vínculo de cobrança — seja porque foi criada antes dessa feature
     * existir, é um Enterprise que virou plano padrão, ou a chamada original
     * falhou (ex.: chave da API ausente na hora do cadastro).
     */
    public function criarAssinatura(Request $request, Empresa $empresa, AsaasClient $asaas)
    {
        $request->validate([
            'plano'          => 'required|in:starter,pro,business,enterprise',
            'ciclo_cobranca' => 'required_unless:plano,enterprise|in:mensal,anual',
        ]);

        $empresa->update([
            'plano'          => $request->plano,
            'ciclo_cobranca' => $request->plano === 'enterprise' ? null : $request->ciclo_cobranca,
        ]);

        if ($request->plano === 'enterprise') {
            return redirect()->route('empresas.show', $empresa)
                ->with('success', 'Plano atualizado. Enterprise não gera assinatura automática no Asaas.');
        }

        $admin = $empresa->usuarios()->where('role', 'admin')->where('status', 'ativo')->first();

        abort_unless($admin, 422, 'Esta empresa não tem nenhum administrador ativo para vincular a assinatura.');

        $this->criarAssinaturaAsaas($asaas, $empresa, $admin, $request->plano, $request->ciclo_cobranca);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Assinatura criada com sucesso no Asaas!');
    }

    public function show(Empresa $empresa)
    {
        $empresa->load('criadoPor');

        $usuarios = $empresa->usuarios()->orderByDesc('role')->orderBy('name')->get();

        // Sem escopo global de propósito: aqui é o super admin olhando de fora,
        // não um usuário da própria empresa — precisa enxergar mesmo sem sessão scoped.
        $resumo = [
            'motoristas'        => Motorista::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'motoristas_ativos' => Motorista::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->where('status', 'ativo')->count(),
            'veiculos'          => Veiculo::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'clientes'          => Cliente::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'viagens'           => Viagem::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
            'viagens_abertas'   => Viagem::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)
                ->whereIn('status', ['aberta', 'em_andamento', 'aguardando_acerto'])->count(),
            'despesas_gerais'   => DespesaGeral::withoutGlobalScope('empresa')->where('empresa_id', $empresa->id)->count(),
        ];

        return view('empresas.show', compact('empresa', 'usuarios', 'resumo'));
    }

    public function edit(Empresa $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nome'            => 'required|string|max:255',
            'cnpj'            => ['nullable', 'string', 'max:20', Rule::unique('empresas', 'cnpj')->ignore($empresa->id)],
            'limite_veiculos' => 'nullable|integer|min:1',
        ]);

        $empresa->update($request->only('nome', 'cnpj', 'limite_veiculos'));

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Ativa a emissão de CT-e/MDF-e para esta empresa: recebe o certificado
     * digital A1 do cliente, registra a empresa como sub-empresa da conta
     * principal da Invexa na Focus NFe, e guarda o token retornado. Ação
     * sempre manual — nunca disparada por self-service do tenant nem por
     * nenhum webhook.
     */
    public function atualizarDadosFiscais(Request $request, Empresa $empresa)
    {
        $dados = $request->validate([
            'cep' => 'nullable|string|max:9',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'codigo_municipio' => 'nullable|string|max:7',
            'uf' => 'nullable|string|max:2',
            'telefone' => 'nullable|string|max:20',
            'inscricao_estadual' => 'nullable|string|max:30',
            'rntrc' => 'nullable|string|max:20',
            'regime_tributario' => 'nullable|string|max:50',
            'cfop_padrao' => 'nullable|string|max:10',
            'icms_situacao_tributaria' => 'nullable|string|max:10',
            'icms_aliquota' => 'nullable|numeric|min:0|max:100',
        ]);

        $empresa->update($dados);

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Dados fiscais atualizados.');
    }

    public function ativarFocusNfe(Request $request, Empresa $empresa, FocusNfeClient $focusNfe)
    {
        $request->validate([
            'ambiente'              => 'required|in:homologacao,producao',
            'certificado'           => 'required|file|mimes:pfx,p12|max:5120',
            'certificado_senha'     => 'required|string|max:100',
            'certificado_validade'  => 'nullable|date',
        ]);

        $certificadoPath = $request->file('certificado')
            ->store('certificados', config('filesystems.uploads_disk'));

        $resultado = $focusNfe->registrarEmpresa(
            ['cnpj' => $empresa->cnpj, 'nome' => $empresa->nome],
            base64_encode($request->file('certificado')->get()),
            $request->certificado_senha,
            $request->ambiente
        );

        if (! $resultado) {
            Storage::disk(config('filesystems.uploads_disk'))->delete($certificadoPath);

            return back()->with('error', 'Não foi possível registrar a empresa no Focus NFe.');
        }

        $empresa->update([
            'focus_nfe_ativo'                => true,
            'focus_nfe_ambiente'             => $request->ambiente,
            'focus_nfe_empresa_id'           => $resultado['id'] ?? null,
            'focus_nfe_token'                => $resultado['token'] ?? null,
            'focus_nfe_status'               => $resultado['status'] ?? 'ativo',
            'focus_nfe_certificado_path'     => $certificadoPath,
            'focus_nfe_certificado_senha'    => $request->certificado_senha,
            'focus_nfe_certificado_validade' => $request->certificado_validade,
        ]);

        Log::info("Focus NFe ativado para a empresa #{$empresa->id} ({$empresa->nome}) por {$request->user()->email}, ambiente={$request->ambiente}.");

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Focus NFe ativado para esta empresa.');
    }

    /**
     * Desliga a emissão para esta empresa sem apagar token/certificado —
     * permite reativar depois sem reenviar o certificado. Emissões já
     * existentes não são afetadas.
     */
    public function desativarFocusNfe(Request $request, Empresa $empresa)
    {
        $empresa->update(['focus_nfe_ativo' => false]);

        Log::info("Focus NFe desativado para a empresa #{$empresa->id} ({$empresa->nome}) por {$request->user()->email}.");

        return redirect()->route('empresas.show', $empresa)
            ->with('success', 'Focus NFe desativado. Emissões existentes não são afetadas.');
    }

    public function toggleStatus(Empresa $empresa)
    {
        $empresa->update(['status' => $empresa->status === 'ativo' ? 'inativo' : 'ativo']);

        return redirect()->route('empresas.index')
            ->with('success', 'Status da empresa atualizado com sucesso!');
    }

    /**
     * Super admin passa a navegar autenticado como o admin da empresa, para
     * dar suporte vendo exatamente o que o cliente vê. A identidade original
     * fica guardada na sessão para poder voltar depois.
     */
    public function iniciarSuporte(Request $request, Empresa $empresa)
    {
        $superAdminId = $request->user()->id;

        $admin = User::where('empresa_id', $empresa->id)
            ->where('role', 'admin')
            ->where('status', 'ativo')
            ->orderBy('id')
            ->first();

        abort_unless($admin, 404, 'Esta empresa não tem nenhum administrador ativo para representar.');

        Auth::login($admin);
        $request->session()->regenerate();
        session([
            'suporte_super_admin_id' => $superAdminId,
            'suporte_empresa_nome'   => $empresa->nome,
        ]);

        Log::info("Suporte iniciado: super admin #{$superAdminId} acessando a empresa #{$empresa->id} ({$empresa->nome}) como {$admin->email}");

        return redirect()->route('dashboard')
            ->with('success', "Acessando como suporte em {$empresa->nome}.");
    }

    public function encerrarSuporte(Request $request)
    {
        $superAdminId = $request->session()->pull('suporte_super_admin_id');
        $request->session()->forget('suporte_empresa_nome');

        abort_unless($superAdminId, 403);

        Auth::loginUsingId($superAdminId);
        $request->session()->regenerate();

        Log::info("Suporte encerrado: voltando para o super admin #{$superAdminId}");

        return redirect()->route('empresas.index')
            ->with('success', 'Modo suporte encerrado.');
    }
}
