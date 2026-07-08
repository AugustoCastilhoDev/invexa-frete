<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class ProgramacoesViagemController extends Controller
{
    public function index(Request $request)
    {
        $status    = $request->input('status', 'pendente');
        $motorista = $request->input('motorista_id');
        $veiculo   = $request->input('veiculo_id');

        $query = ProgramacaoViagem::with(['motorista', 'veiculo', 'cliente'])
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

        $programacoes = $query->paginate(15)->withQueryString();

        $veiculosComProgramacaoPendente = ProgramacaoViagem::where('status', 'pendente')->pluck('veiculo_id');

        $totalPendentes = ProgramacaoViagem::where('status', 'pendente')->count();
        $totalVeiculosSemProgramacao = Veiculo::where('status', 'ativo')
            ->whereNotIn('id', $veiculosComProgramacaoPendente)
            ->count();

        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
        $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();

        return view('programacoes.index', compact(
            'programacoes', 'motoristas', 'veiculos',
            'totalPendentes', 'totalVeiculosSemProgramacao'
        ));
    }

    public function create(Request $request)
    {
        $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
        $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();
        $clientes   = Cliente::where('status', 'ativo')->orderBy('nome')->get();

        return view('programacoes.create', compact('motoristas', 'veiculos', 'clientes'));
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
        $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();
        $clientes   = Cliente::where('status', 'ativo')->orderBy('nome')->get();

        return view('programacoes.edit', compact('programacao', 'motoristas', 'veiculos', 'clientes'));
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
            'cliente_id'       => 'nullable|exists:clientes,id',
            'origem'           => 'required|string|max:255',
            'destino'          => 'required|string|max:255',
            'valor_frete'      => 'nullable|numeric|min:0',
            'data_prevista'    => 'required|date',
            'observacoes'      => 'nullable|string',
            'viagem_origem_id' => 'nullable|exists:viagens,id',
        ]);

        return $data;
    }
}
