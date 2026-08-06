<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramacoesViagemController extends Controller
{
    public function index(Request $request)
    {
        $status      = $request->input('status', 'pendente');
        $motorista   = $request->input('motorista_id');
        $veiculo     = $request->input('veiculo_id');
        $periodo     = $request->input('periodo');
        $riscoNoShow = $request->boolean('risco_no_show');

        $query = ProgramacaoViagem::with(['motorista', 'veiculo', 'carreta', 'cliente'])
            ->orderBy('data_prevista');

        if ($status !== 'todas') {
            $query->where('status', $status);
        }

        if ($motorista) {
            $query->where('motorista_id', $motorista);
        }

        if ($veiculo) {
            $query->where('veiculo_id', $veiculo);
        }

        if ($periodo === 'hoje') {
            $query->whereDate('data_prevista', now()->toDateString());
        } elseif ($periodo === 'amanha') {
            $query->whereDate('data_prevista', now()->addDay()->toDateString());
        } elseif ($periodo === 'semana') {
            $query->whereBetween('data_prevista', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        }

        // Reaproveita o mesmo filtro em PHP do card (ver ProgramacaoViagem::emRiscoDeNoShow) —
        // a lista de IDs em risco entra como whereIn, mantendo paginação e demais filtros funcionando juntos.
        if ($riscoNoShow) {
            $query->whereIn('id', ProgramacaoViagem::emRiscoDeNoShow()->pluck('id'));
        }

        $programacoes = $query->paginate(15)->withQueryString();

        // Cards no topo mostram sempre o total geral, independente dos filtros da tabela.
        $veiculosComProgramacaoPendente = ProgramacaoViagem::where('status', 'pendente')->pluck('veiculo_id');

        $totalPendentes = ProgramacaoViagem::where('status', 'pendente')->count();

        $totalRiscoNoShow = ProgramacaoViagem::emRiscoDeNoShow()->count();

        // Carreta vinculada a um cavalo não conta separadamente: ela sempre viaja
        // junto do cavalo, então só o cavalo precisa da própria programação.
        $veiculosSemProgramacao = Veiculo::contamParaLimite()
            ->where('status', 'ativo')
            ->whereNotIn('id', $veiculosComProgramacaoPendente)
            ->orderBy('placa')
            ->get();
        $totalVeiculosSemProgramacao = $veiculosSemProgramacao->count();

        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
        $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();

        return view('programacoes.index', compact(
            'programacoes', 'motoristas', 'veiculos',
            'totalPendentes', 'totalVeiculosSemProgramacao', 'totalRiscoNoShow',
            'veiculosSemProgramacao', 'periodo', 'riscoNoShow'
        ));
    }

    public function create(Request $request)
    {
        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
        // Carreta não anda sozinha — não entra como opção de "Veículo" da viagem;
        // quem usa carreta escolhe o cavalo aqui e a carreta no campo abaixo.
        $veiculos = Veiculo::with('carretas')->where('status', 'ativo')->where('tipo', '!=', 'carreta')->orderBy('placa')->get();
        $carretas = Veiculo::where('status', 'ativo')->where('tipo', 'carreta')->orderBy('placa')->get();
        $clientes = Cliente::where('status', 'ativo')->orderBy('nome')->get();

        return view('programacoes.create', compact('motoristas', 'veiculos', 'carretas', 'clientes'));
    }

    public function store(Request $request)
    {
        $data = $this->validarDados($request);

        ProgramacaoViagem::create($data);

        return redirect()->route('programacoes.index')
            ->with('success', 'Próxima viagem programada com sucesso!');
    }

    public function edit(ProgramacaoViagem $programacao)
    {
        abort_if(! $programacao->estaPendente(), 400, 'Esta programação já foi confirmada.');

        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
        $veiculos = Veiculo::with('carretas')->where('status', 'ativo')->where('tipo', '!=', 'carreta')->orderBy('placa')->get();
        $carretas = Veiculo::where('status', 'ativo')->where('tipo', 'carreta')->orderBy('placa')->get();
        $clientes = Cliente::where('status', 'ativo')->orderBy('nome')->get();

        return view('programacoes.edit', compact('programacao', 'motoristas', 'veiculos', 'carretas', 'clientes'));
    }

    public function update(Request $request, ProgramacaoViagem $programacao)
    {
        abort_if(! $programacao->estaPendente(), 400, 'Esta programação já foi confirmada.');

        $data = $this->validarDados($request, $programacao);

        $programacao->update($data);

        return redirect()->route('programacoes.index')
            ->with('success', 'Programação atualizada com sucesso!');
    }

    public function destroy(ProgramacaoViagem $programacao)
    {
        $programacao->delete();

        return redirect()->route('programacoes.index')
            ->with('success', 'Programação removida com sucesso!');
    }

    public function marcarChegada(Request $request, ProgramacaoViagem $programacao)
    {
        abort_if(! $programacao->estaPendente(), 400, 'Esta programação já foi confirmada.');

        $request->validate(['horario' => 'nullable|date_format:H:i']);

        $programacao->marcarChegada($request->input('horario') ?: now()->format('H:i'));

        return redirect()->route('programacoes.index')
            ->with('success', 'Chegada no local de coleta registrada com sucesso!');
    }

    private function validarDados(Request $request, ?ProgramacaoViagem $programacao = null): array
    {
        $data = $request->validate([
            'motorista_id' => [
                'required', 'exists:motoristas,id',
                function ($attribute, $value, $fail) use ($programacao) {
                    $existe = ProgramacaoViagem::where('motorista_id', $value)
                        ->where('status', 'pendente')
                        ->when($programacao, fn ($q) => $q->whereKeyNot($programacao->id))
                        ->exists();

                    if ($existe) {
                        $fail('Este motorista já tem uma próxima viagem programada.');
                    }
                },
            ],
            'veiculo_id' => [
                'required', 'exists:veiculos,id',
                function ($attribute, $value, $fail) use ($programacao) {
                    $existe = ProgramacaoViagem::where('veiculo_id', $value)
                        ->where('status', 'pendente')
                        ->when($programacao, fn ($q) => $q->whereKeyNot($programacao->id))
                        ->exists();

                    if ($existe) {
                        $fail('Este veículo já tem uma próxima viagem programada.');
                    }
                },
            ],
            'carreta_id' => ['nullable', Rule::exists('veiculos', 'id')->where('tipo', 'carreta')],
            'cliente_id'       => 'nullable|exists:clientes,id',
            'origem'           => 'required|string|max:255',
            'origem_uf'        => 'nullable|string|max:2',
            'origem_codigo_municipio' => 'nullable|string|max:7',
            'destino'          => 'required|string|max:255',
            'destino_uf'       => 'nullable|string|max:2',
            'destino_codigo_municipio' => 'nullable|string|max:7',
            'valor_frete'      => 'nullable|numeric|min:0',
            'data_prevista'    => 'required|date',
            'hora_coleta'      => 'nullable|date_format:H:i',
            'data_entrega_prevista' => 'nullable|date|after_or_equal:data_prevista',
            'hora_entrega_prevista' => 'nullable|date_format:H:i',
            'observacoes'      => 'nullable|string',
            'viagem_origem_id' => 'nullable|exists:viagens,id',
        ]);

        return $data;
    }
}
