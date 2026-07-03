<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cliente;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ViagensController extends Controller
{
    public function index()
{
    $status     = request('status', 'todas');
    $motorista  = request('motorista_id');
    $veiculo    = request('veiculo_id');
    $dataInicio = request('data_inicio');
    $dataFim    = request('data_fim');

    $query = Viagem::with(['motorista', 'veiculo'])
        ->orderByDesc('created_at');

    if ($status !== 'todas') {
        $query->where('status', $status);
    }

    if ($motorista) {
        $query->where('motorista_id', $motorista);
    }

    if ($veiculo) {
        $query->where('veiculo_id', $veiculo);
    }

    if ($dataInicio) {
        $query->whereDate('data_saida', '>=', $dataInicio);
    }

    if ($dataFim) {
        $query->whereDate('data_saida', '<=', $dataFim);
    }

    $viagens    = $query->paginate(15)->withQueryString();
    $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
    $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();

    return view('viagens.index', compact(
        'viagens',
        'motoristas',
        'veiculos'
    ));
}

    public function create()
{
    $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
    $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();
    $clientes   = Cliente::where('status', 'ativo')->orderBy('nome')->get();
    return view('viagens.create', compact('motoristas', 'veiculos', 'clientes'));
}

    public function store(Request $request)
    {
        $request->validate([
            'motorista_id'         => 'required|exists:motoristas,id',
            'veiculo_id'           => 'required|exists:veiculos,id',
            'origem'               => 'required|string|max:255',
            'destino'              => 'required|string|max:255',
            'cliente_id'           => 'nullable|exists:clientes,id',
            'data_saida'           => 'required|date',
            'km_inicial'           => 'nullable|integer|min:0',
            'valor_frete'          => 'required|numeric|min:0',
            'percentual_motorista' => 'required|numeric|min:0|max:100',
            'valor_adiantamento'   => 'nullable|numeric|min:0',
            'observacoes'          => 'nullable|string',
        ]);

        $data = $request->only([
            'motorista_id', 'veiculo_id', 'origem', 'destino', 'cliente_id',
            'data_saida', 'km_inicial', 'valor_frete', 'percentual_motorista',
            'observacoes',
        ]);

        $data['valor_adiantamento'] = $request->input('valor_adiantamento', 0) ?? 0;
        $data['adiantamento_descontavel'] = $request->has('adiantamento_descontavel');

        $viagem = Viagem::create($data);

        // Recalcula com a mesma lógica usada em toda atualização, garantindo
        // que o saldo já nasça respeitando adiantamento_descontavel.
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Viagem aberta com sucesso!');
    }

    public function show(Viagem $viagem)
    {
        $viagem->load([
            'motorista', 'veiculo', 'cliente',
            'criadoPor', 'atualizadoPor',
            'lancamentos.criadoPor', 'descontos.criadoPor', 'documentos.criadoPor',
        ]);
        return view('viagens.show', compact('viagem'));
    }

    public function edit(Viagem $viagem)
{
    $motoristas = Motorista::where('status', 'ativo')->orderBy('nome')->get();
    $veiculos   = Veiculo::where('status', 'ativo')->orderBy('placa')->get();
    $clientes   = Cliente::where('status', 'ativo')->orderBy('nome')->get();
    return view('viagens.edit', compact('viagem', 'motoristas', 'veiculos', 'clientes'));
}

    public function update(Request $request, Viagem $viagem)
    {
        $request->validate([
            'motorista_id'         => 'required|exists:motoristas,id',
            'veiculo_id'           => 'required|exists:veiculos,id',
            'origem'               => 'required|string|max:255',
            'destino'              => 'required|string|max:255',
            'cliente_id'           => 'nullable|exists:clientes,id',
            'data_saida'           => 'required|date',
            'data_retorno'         => 'nullable|date|after_or_equal:data_saida',
            'km_inicial'           => 'nullable|integer|min:0',
            'km_final'             => 'nullable|integer|min:0',
            'valor_frete'          => 'required|numeric|min:0',
            'percentual_motorista' => 'required|numeric|min:0|max:100',
            'valor_adiantamento'   => 'nullable|numeric|min:0',
            'status'               => 'required|in:aberta,em_andamento,aguardando_acerto,encerrada',
            'observacoes'          => 'nullable|string',
        ]);

        $data = $request->all();

        $data['valor_adiantamento'] = $request->input('valor_adiantamento', 0) ?? 0;
        $data['adiantamento_descontavel'] = $request->has('adiantamento_descontavel');

        $viagem->update($data);
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Viagem atualizada com sucesso!');
    }

    public function destroy(Viagem $viagem)
    {
        $viagem->delete();
        return redirect()->route('viagens.index')
            ->with('success', 'Viagem removida com sucesso!');
    }

    public function avancarStatus(Viagem $viagem)
    {
        $proximo = $viagem->proximo_status;

        abort_if(! $proximo, 400, 'Não há um próximo status para esta viagem.');

        $viagem->update(['status' => $proximo]);
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Status da viagem atualizado com sucesso!');
    }

    public function encerrar(Viagem $viagem)
    {
        $viagem->update([
            'status'       => 'encerrada',
            'data_retorno' => now(),
        ]);
        $viagem->recalcularTotais();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Viagem encerrada com sucesso!');
    }

    public function assinar(Request $request, Viagem $viagem)
    {
        abort_unless($viagem->podeSerAssinada(), 400, 'Esta viagem ainda não está pronta para acerto.');

        $request->validate([
            'assinatura' => ['required', 'string', 'regex:/^data:image\/png;base64,/'],
        ]);

        $base64  = substr($request->input('assinatura'), strlen('data:image/png;base64,'));
        $binario = base64_decode($base64, true);

        if ($binario === false || strlen($binario) > 500 * 1024 || @getimagesizefromstring($binario) === false) {
            throw ValidationException::withMessages(['assinatura' => 'Assinatura inválida.']);
        }

        $disco   = config('filesystems.uploads_disk');
        $caminho = 'assinaturas/viagem-' . $viagem->id . '-' . (string) Str::uuid() . '.png';

        Storage::disk($disco)->put($caminho, $binario);

        if ($viagem->assinatura_motorista_path) {
            Storage::disk($disco)->delete($viagem->assinatura_motorista_path);
        }

        $viagem->forceFill([
            'assinatura_motorista_path' => $caminho,
            'assinatura_motorista_em'   => now(),
        ])->save();

        return redirect()->route('viagens.show', $viagem)
            ->with('success', 'Assinatura do motorista registrada com sucesso!');
    }

    public function imprimir(Viagem $viagem)
    {
        $viagem->load(['motorista', 'veiculo', 'lancamentos', 'descontos']);

        $assinaturaBase64 = null;
        if ($viagem->assinatura_motorista_path) {
            $conteudo = Storage::disk(config('filesystems.uploads_disk'))->get($viagem->assinatura_motorista_path);
            $assinaturaBase64 = $conteudo ? 'data:image/png;base64,' . base64_encode($conteudo) : null;
        }

        $pdf = Pdf::loadView('viagens.imprimir', compact('viagem', 'assinaturaBase64'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('acerto-viagem-' . $viagem->id . '.pdf');
    }
}