<?php

namespace App\Http\Controllers;

use App\Models\DespesaGeral;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DespesasGeraisController extends Controller
{
    private const CATEGORIAS = ['aluguel', 'salarios', 'contas', 'seguro', 'impostos', 'marketing', 'outros'];

    public function index(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim    = $request->input('data_fim', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $categoria  = $request->input('categoria');

        $despesas = DespesaGeral::noPeriodo($dataInicio, $dataFim)
            ->when($categoria, fn ($query) => $query->where('categoria', $categoria))
            ->orderByDesc('data_despesa')
            ->paginate(15)
            ->withQueryString();

        $total = DespesaGeral::noPeriodo($dataInicio, $dataFim)
            ->when($categoria, fn ($query) => $query->where('categoria', $categoria))
            ->sum('valor');

        return view('despesas-gerais.index', compact('despesas', 'total', 'dataInicio', 'dataFim', 'categoria'));
    }

    public function create()
    {
        return view('despesas-gerais.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'categoria'    => 'required|in:' . implode(',', self::CATEGORIAS),
            'descricao'    => 'required|string|max:255',
            'valor'        => 'required|numeric|min:0',
            'data_despesa' => 'required|date',
            'recorrente'   => 'nullable|boolean',
            'observacao'   => 'nullable|string',
        ]);

        $data = $request->all();
        $data['recorrente'] = $request->has('recorrente');

        DespesaGeral::create($data);

        return redirect()->route('despesas-gerais.index')
            ->with('success', 'Despesa registrada com sucesso!');
    }

    public function edit(DespesaGeral $despesaGeral)
    {
        return view('despesas-gerais.edit', ['despesa' => $despesaGeral]);
    }

    public function update(Request $request, DespesaGeral $despesaGeral)
    {
        $request->validate([
            'categoria'    => 'required|in:' . implode(',', self::CATEGORIAS),
            'descricao'    => 'required|string|max:255',
            'valor'        => 'required|numeric|min:0',
            'data_despesa' => 'required|date',
            'recorrente'   => 'nullable|boolean',
            'observacao'   => 'nullable|string',
        ]);

        $data = $request->all();
        $data['recorrente'] = $request->has('recorrente');

        $despesaGeral->update($data);

        return redirect()->route('despesas-gerais.index')
            ->with('success', 'Despesa atualizada com sucesso!');
    }

    public function destroy(DespesaGeral $despesaGeral)
    {
        $despesaGeral->delete();

        return redirect()->route('despesas-gerais.index')
            ->with('success', 'Despesa removida com sucesso!');
    }
}
